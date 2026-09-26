import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';

class HafalanInputPage extends StatefulWidget {
  final int studentId;
  const HafalanInputPage({super.key, required this.studentId});

  @override
  State<HafalanInputPage> createState() => _HafalanInputPageState();
}

class _HafalanInputPageState extends State<HafalanInputPage> {
  final _surah     = TextEditingController();
  final _ayahStart = TextEditingController();
  final _ayahEnd   = TextEditingController();
  final _note      = TextEditingController();
  String _quality  = 'good';
  bool _submitting = false;

  Future<void> _submit() async {
    setState(() => _submitting = true);
    try {
      await ApiClient.dio.post<dynamic>(ApiEndpoints.hafalanRecord, data: <String, dynamic>{
        'student_id'   : widget.studentId,
        'surah'        : _surah.text,
        'ayah_start'   : int.tryParse(_ayahStart.text) ?? 1,
        'ayah_end'     : int.tryParse(_ayahEnd.text) ?? 1,
        'memorized_at' : DateTime.now().toIso8601String().split('T').first,
        'quality'      : _quality,
        'note'         : _note.text,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Setoran tersimpan')));
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e')));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('📖 Setoran Hafalan')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        TextField(controller: _surah, decoration: const InputDecoration(labelText: 'Surah', hintText: 'Al-Baqarah', border: OutlineInputBorder())),
        const SizedBox(height: 12),
        Row(children: [
          Expanded(child: TextField(
            controller: _ayahStart,
            decoration: const InputDecoration(labelText: 'Ayat awal', border: OutlineInputBorder()),
            keyboardType: TextInputType.number,
          )),
          const SizedBox(width: 12),
          Expanded(child: TextField(
            controller: _ayahEnd,
            decoration: const InputDecoration(labelText: 'Ayat akhir', border: OutlineInputBorder()),
            keyboardType: TextInputType.number,
          )),
        ]),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(
          initialValue: _quality,
          decoration: const InputDecoration(labelText: 'Kualitas', border: OutlineInputBorder()),
          items: const [
            DropdownMenuItem(value: 'excellent', child: Text('🌟 Excellent')),
            DropdownMenuItem(value: 'good', child: Text('✅ Good')),
            DropdownMenuItem(value: 'fair', child: Text('🆗 Fair')),
            DropdownMenuItem(value: 'needs_review', child: Text('⚠️ Needs Review')),
          ],
          onChanged: (String? v) => setState(() => _quality = v ?? 'good'),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _note,
          maxLines: 3,
          decoration: const InputDecoration(labelText: 'Catatan ustadz/musyrif', border: OutlineInputBorder()),
        ),
        const SizedBox(height: 24),
        FilledButton.icon(
          onPressed: _submitting ? null : _submit,
          icon: _submitting
              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
              : const Icon(Icons.check),
          label: Text(_submitting ? 'Menyimpan...' : 'Simpan Setoran'),
        ),
      ]),
    );
  }
}
