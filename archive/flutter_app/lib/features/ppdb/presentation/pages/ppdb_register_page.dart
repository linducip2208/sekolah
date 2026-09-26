import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';
import '../../../../core/api/response_unwrap.dart';

class PpdbRegisterPage extends StatefulWidget {
  final String subdomain;
  const PpdbRegisterPage({super.key, required this.subdomain});

  @override
  State<PpdbRegisterPage> createState() => _PpdbRegisterPageState();
}

class _PpdbRegisterPageState extends State<PpdbRegisterPage> {
  final _formKey = GlobalKey<FormState>();
  final _studentName = TextEditingController();
  final _nisn = TextEditingController();
  final _dob = TextEditingController();
  final _address = TextEditingController();
  final _district = TextEditingController();
  final _city = TextEditingController();
  final _previousSchool = TextEditingController();
  final _parentName = TextEditingController();
  final _parentPhone = TextEditingController();
  final _parentEmail = TextEditingController();
  final _averageScore = TextEditingController();

  String _gender = 'male';
  String _jalur  = 'reguler';
  int? _periodId;
  List<Map<String, dynamic>> _periods = <Map<String, dynamic>>[];
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _loadPeriods();
  }

  Future<void> _loadPeriods() async {
    try {
      final Response<dynamic> r = await ApiClient.dio
          .get<dynamic>(ApiEndpoints.ppdbPeriods(widget.subdomain));
      final Map<String, dynamic> body = unwrapMap(r.data);
      final List<dynamic> list = body['data'] as List<dynamic>? ?? <dynamic>[];
      setState(() => _periods = list.cast<Map<String, dynamic>>());
    } catch (_) {}
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_periodId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih periode PPDB dulu')),
      );
      return;
    }
    setState(() => _loading = true);
    try {
      await ApiClient.dio.post<dynamic>(
        ApiEndpoints.ppdbRegister(widget.subdomain),
        data: <String, dynamic>{
          'ppdb_period_id'  : _periodId,
          'jalur'           : _jalur,
          'student_name'    : _studentName.text,
          'nisn'            : _nisn.text.isEmpty ? null : _nisn.text,
          'date_of_birth'   : _dob.text,
          'gender'          : _gender,
          'address'         : _address.text,
          'district'        : _district.text,
          'city'            : _city.text,
          'previous_school' : _previousSchool.text.isEmpty ? null : _previousSchool.text,
          'parent_name'     : _parentName.text,
          'parent_phone'    : _parentPhone.text,
          'parent_email'    : _parentEmail.text,
          'average_score'   : double.tryParse(_averageScore.text),
        },
      );
      if (!mounted) return;
      showDialog<void>(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('✅ Pendaftaran Berhasil'),
          content: const Text('Selanjutnya, login dan submit pendaftaran setelah upload semua dokumen.'),
          actions: [TextButton(onPressed: () => Navigator.pop(context), child: const Text('OK'))],
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e')));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('PPDB Online')),
      body: Form(
        key: _formKey,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          DropdownButtonFormField<int>(
            initialValue: _periodId,
            decoration: const InputDecoration(labelText: 'Periode PPDB', border: OutlineInputBorder()),
            items: _periods.map((Map<String, dynamic> p) => DropdownMenuItem<int>(
              value: p['id'] as int,
              child: Text(p['name']?.toString() ?? '-'),
            )).toList(),
            onChanged: (int? v) => setState(() => _periodId = v),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _jalur,
            decoration: const InputDecoration(labelText: 'Jalur Pendaftaran', border: OutlineInputBorder()),
            items: const [
              DropdownMenuItem(value: 'reguler', child: Text('Reguler')),
              DropdownMenuItem(value: 'zonasi', child: Text('Zonasi')),
              DropdownMenuItem(value: 'prestasi', child: Text('Prestasi')),
              DropdownMenuItem(value: 'afirmasi', child: Text('Afirmasi (Tidak Mampu)')),
              DropdownMenuItem(value: 'undian', child: Text('Undian')),
            ],
            onChanged: (String? v) => setState(() => _jalur = v ?? 'reguler'),
          ),
          const Divider(height: 32),
          const Text('Data Siswa', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          TextFormField(controller: _studentName, decoration: const InputDecoration(labelText: 'Nama Siswa', border: OutlineInputBorder()), validator: _required),
          const SizedBox(height: 8),
          TextFormField(controller: _nisn, decoration: const InputDecoration(labelText: 'NISN (opsional, 10 digit)', border: OutlineInputBorder()), keyboardType: TextInputType.number),
          const SizedBox(height: 8),
          TextFormField(controller: _dob, decoration: const InputDecoration(labelText: 'Tanggal Lahir (YYYY-MM-DD)', border: OutlineInputBorder()), validator: _required),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            initialValue: _gender,
            decoration: const InputDecoration(labelText: 'Jenis Kelamin', border: OutlineInputBorder()),
            items: const [
              DropdownMenuItem(value: 'male', child: Text('Laki-laki')),
              DropdownMenuItem(value: 'female', child: Text('Perempuan')),
            ],
            onChanged: (String? v) => setState(() => _gender = v ?? 'male'),
          ),
          const SizedBox(height: 8),
          TextFormField(controller: _previousSchool, decoration: const InputDecoration(labelText: 'Sekolah Sebelumnya', border: OutlineInputBorder())),
          const SizedBox(height: 8),
          TextFormField(controller: _averageScore, decoration: const InputDecoration(labelText: 'Rata-rata Nilai Rapor', border: OutlineInputBorder()), keyboardType: TextInputType.number),
          const Divider(height: 32),
          const Text('Alamat', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          TextFormField(controller: _address, maxLines: 2, decoration: const InputDecoration(labelText: 'Alamat Lengkap', border: OutlineInputBorder()), validator: _required),
          const SizedBox(height: 8),
          TextFormField(controller: _district, decoration: const InputDecoration(labelText: 'Kecamatan', border: OutlineInputBorder()), validator: _required),
          const SizedBox(height: 8),
          TextFormField(controller: _city, decoration: const InputDecoration(labelText: 'Kota', border: OutlineInputBorder()), validator: _required),
          const Divider(height: 32),
          const Text('Data Orang Tua', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          TextFormField(controller: _parentName, decoration: const InputDecoration(labelText: 'Nama Orang Tua', border: OutlineInputBorder()), validator: _required),
          const SizedBox(height: 8),
          TextFormField(controller: _parentPhone, decoration: const InputDecoration(labelText: 'No. HP', border: OutlineInputBorder()), validator: _required, keyboardType: TextInputType.phone),
          const SizedBox(height: 8),
          TextFormField(controller: _parentEmail, decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder()), validator: _required, keyboardType: TextInputType.emailAddress),
          const SizedBox(height: 24),
          FilledButton.icon(
            onPressed: _loading ? null : _submit,
            icon: _loading
                ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.send),
            label: Text(_loading ? 'Mengirim...' : 'Daftar'),
          ),
        ]),
      ),
    );
  }

  String? _required(String? v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null;
}
