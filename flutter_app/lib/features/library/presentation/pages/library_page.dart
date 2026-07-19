import 'package:flutter/material.dart';

import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../data/library_repository.dart';
import 'barcode_scanner_page.dart';

class LibraryPage extends StatefulWidget {
  const LibraryPage({super.key});

  @override
  State<LibraryPage> createState() => _LibraryPageState();
}

class _LibraryPageState extends State<LibraryPage> {
  final LibraryRepository _repo = LibraryRepository();
  final TextEditingController _q = TextEditingController();
  late Future<List<Map<String, dynamic>>> _future = _repo.books();

  void _reload() => setState(() => _future = _repo.books(query: _q.text));

  @override
  void dispose() {
    _q.dispose();
    super.dispose();
  }

  Future<void> _scan() async {
    final String? code = await Navigator.of(context).push(
      MaterialPageRoute<String>(builder: (_) => const BarcodeScannerPage()),
    );
    if (code == null) return;
    setState(() => _q.text = code);
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Perpustakaan'),
        actions: <Widget>[
          IconButton(
            icon: const Icon(Icons.qr_code_scanner_outlined),
            onPressed: _scan,
            tooltip: 'Pindai barcode',
          ),
        ],
      ),
      body: Column(
        children: <Widget>[
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _q,
              decoration: const InputDecoration(
                hintText: 'Cari judul atau kode buku...',
                prefixIcon: Icon(Icons.search),
              ),
              onSubmitted: (_) => _reload(),
            ),
          ),
          Expanded(
            child: FutureBuilder<List<Map<String, dynamic>>>(
              future: _future,
              builder: (BuildContext c,
                  AsyncSnapshot<List<Map<String, dynamic>>> snap) {
                if (snap.connectionState == ConnectionState.waiting) {
                  return const AppLoading();
                }
                if (snap.hasError) {
                  return AppError(message: '${snap.error}', onRetry: _reload);
                }
                final List<Map<String, dynamic>> list =
                    snap.data ?? <Map<String, dynamic>>[];
                if (list.isEmpty) {
                  return const AppEmpty(title: 'Tidak ada buku ditemukan');
                }
                return ListView.separated(
                  padding: const EdgeInsets.all(12),
                  itemCount: list.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (BuildContext c, int i) {
                    final Map<String, dynamic> b = list[i];
                    return Card(
                      child: ListTile(
                        leading: const Icon(Icons.menu_book_outlined),
                        title: Text(b['title'] as String? ?? '-'),
                        subtitle: Text(
                            '${b['author'] ?? '-'} • ${b['code'] ?? ''}'),
                        trailing: Text('Stok: ${b['available_qty'] ?? 0}',
                            style: Theme.of(context).textTheme.bodySmall),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
