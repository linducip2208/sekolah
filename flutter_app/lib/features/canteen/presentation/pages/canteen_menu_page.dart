import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';

class CanteenMenuPage extends StatefulWidget {
  final int studentId;
  const CanteenMenuPage({super.key, required this.studentId});

  @override
  State<CanteenMenuPage> createState() => _CanteenMenuPageState();
}

class _CanteenMenuPageState extends State<CanteenMenuPage> {
  late Future<Map<String, dynamic>> _menuFuture;
  Future<Map<String, dynamic>>? _walletFuture;
  final Map<int, int> _cart = <int, int>{};
  final Map<int, Map<String, dynamic>> _menuById = <int, Map<String, dynamic>>{};

  @override
  void initState() {
    super.initState();
    _menuFuture = _loadMenu();
    _walletFuture = _loadWallet();
  }

  Future<Map<String, dynamic>> _loadMenu() async {
    final Response<dynamic> r = await ApiClient.dio.get<dynamic>(ApiEndpoints.canteenMenu);
    final Map<String, dynamic> body = r.data is Map<String, dynamic>
        ? r.data as Map<String, dynamic>
        : <String, dynamic>{};
    final List<dynamic> items = body['items'] as List<dynamic>? ?? <dynamic>[];
    for (final dynamic m in items) {
      if (m is Map<String, dynamic>) _menuById[m['id'] as int] = m;
    }
    return body;
  }

  Future<Map<String, dynamic>> _loadWallet() async {
    final Response<dynamic> r = await ApiClient.dio.get<dynamic>(ApiEndpoints.canteenWallet(widget.studentId));
    return r.data is Map<String, dynamic> ? r.data as Map<String, dynamic> : <String, dynamic>{};
  }

  int get _cartTotal {
    int total = 0;
    _cart.forEach((int id, int qty) {
      final Map<String, dynamic>? m = _menuById[id];
      if (m != null) total += (m['price'] as int? ?? 0) * qty;
    });
    return total;
  }

  Future<void> _placeOrder() async {
    if (_cart.isEmpty) return;
    try {
      final List<Map<String, dynamic>> items = _cart.entries.map((MapEntry<int, int> e) => <String, dynamic>{
        'menu_item_id': e.key,
        'qty': e.value,
      }).toList();

      await ApiClient.dio.post<dynamic>(ApiEndpoints.canteenOrder, data: <String, dynamic>{
        'student_id': widget.studentId,
        'items': items,
        'source': 'preorder',
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Order berhasil!')));
      setState(() {
        _cart.clear();
        _walletFuture = _loadWallet();
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('🍱 Kantin'),
        actions: [
          FutureBuilder<Map<String, dynamic>>(
            future: _walletFuture,
            builder: (_, AsyncSnapshot<Map<String, dynamic>> snap) {
              final int balance = (snap.data?['balance'] as int?) ?? 0;
              return Padding(
                padding: const EdgeInsets.only(right: 12),
                child: Center(
                  child: Chip(
                    avatar: const Icon(Icons.account_balance_wallet, size: 14),
                    label: Text('Rp ${(balance / 100).toStringAsFixed(0)}'),
                  ),
                ),
              );
            },
          ),
        ],
      ),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _menuFuture,
        builder: (BuildContext c, AsyncSnapshot<Map<String, dynamic>> snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          final List<dynamic> items = snap.data?['items'] as List<dynamic>? ?? <dynamic>[];
          if (items.isEmpty) return const Center(child: Text('Menu kosong hari ini'));

          return ListView.separated(
            itemCount: items.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (_, int i) {
              final Map<String, dynamic> m = items[i] as Map<String, dynamic>;
              final int id = m['id'] as int;
              final int qty = _cart[id] ?? 0;
              return ListTile(
                title: Text(m['name']?.toString() ?? '-'),
                subtitle: Text('Rp ${((m['price'] ?? 0) / 100).toStringAsFixed(0)}'),
                trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                  IconButton(
                    icon: const Icon(Icons.remove_circle_outline),
                    onPressed: qty > 0 ? () => setState(() => _cart[id] = qty - 1) : null,
                  ),
                  Text('$qty', style: const TextStyle(fontWeight: FontWeight.bold)),
                  IconButton(
                    icon: const Icon(Icons.add_circle_outline),
                    onPressed: () => setState(() => _cart[id] = qty + 1),
                  ),
                ]),
              );
            },
          );
        },
      ),
      bottomNavigationBar: _cart.isEmpty
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: FilledButton(
                  onPressed: _placeOrder,
                  child: Text('Checkout — Rp ${(_cartTotal / 100).toStringAsFixed(0)}'),
                ),
              ),
            ),
    );
  }
}
