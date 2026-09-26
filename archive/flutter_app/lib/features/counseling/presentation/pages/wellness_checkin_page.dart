import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';

class WellnessCheckinPage extends StatefulWidget {
  final int studentId;
  const WellnessCheckinPage({super.key, required this.studentId});

  @override
  State<WellnessCheckinPage> createState() => _WellnessCheckinPageState();
}

class _WellnessCheckinPageState extends State<WellnessCheckinPage> {
  int _mood = 7;
  final Set<String> _tags = <String>{};
  final TextEditingController _noteCtrl = TextEditingController();
  bool _submitting = false;

  static const List<String> _availableTags = <String>[
    'happy', 'sad', 'anxious', 'tired', 'excited',
    'lonely', 'angry', 'calm', 'stressed', 'grateful',
  ];

  Future<void> _submit() async {
    setState(() => _submitting = true);
    try {
      await ApiClient.dio.post<dynamic>(ApiEndpoints.wellnessCheckin, data: <String, dynamic>{
        'student_id'   : widget.studentId,
        'mood_score'   : _mood,
        'feeling_tags' : _tags.toList(),
        'note'         : _noteCtrl.text,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Check-in tersimpan. Terima kasih!')),
      );
      Navigator.of(context).pop(true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e')));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final Color moodColor = _mood >= 7 ? Colors.green : _mood >= 4 ? Colors.orange : Colors.red;

    return Scaffold(
      appBar: AppBar(title: const Text('Bagaimana perasaanmu hari ini?')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        const Text('Mood (1=sedih sekali, 10=sangat senang)',
            style: TextStyle(fontWeight: FontWeight.bold)),
        const SizedBox(height: 12),
        Center(child: Text('$_mood',
            style: TextStyle(fontSize: 64, fontWeight: FontWeight.bold, color: moodColor))),
        Slider(
          value: _mood.toDouble(),
          min: 1, max: 10, divisions: 9,
          activeColor: moodColor,
          label: _mood.toString(),
          onChanged: (double v) => setState(() => _mood = v.round()),
        ),
        const SizedBox(height: 24),
        const Text('Pilih perasaan yang sesuai (boleh lebih dari 1)',
            style: TextStyle(fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        Wrap(spacing: 8, runSpacing: 8, children: _availableTags.map((String tag) {
          final bool selected = _tags.contains(tag);
          return FilterChip(
            label: Text(tag),
            selected: selected,
            onSelected: (bool s) => setState(() {
              s ? _tags.add(tag) : _tags.remove(tag);
            }),
          );
        }).toList()),
        const SizedBox(height: 24),
        TextField(
          controller: _noteCtrl,
          maxLines: 4,
          decoration: const InputDecoration(
            labelText: 'Cerita tambahan (opsional, privat untuk konselor)',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 24),
        FilledButton.icon(
          onPressed: _submitting ? null : _submit,
          icon: _submitting
              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
              : const Icon(Icons.send),
          label: Text(_submitting ? 'Mengirim...' : 'Kirim Check-in'),
        ),
      ]),
    );
  }
}
