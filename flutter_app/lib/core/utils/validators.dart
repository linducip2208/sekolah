class Validators {
  Validators._();

  static String? required(String? v, {String label = 'Field'}) {
    if (v == null || v.trim().isEmpty) return '$label tidak boleh kosong';
    return null;
  }

  static String? email(String? v) {
    if (v == null || v.trim().isEmpty) return 'Email wajib diisi';
    final RegExp re = RegExp(r'^[\w.+-]+@([\w-]+\.)+[\w-]{2,}$');
    return re.hasMatch(v.trim()) ? null : 'Format email tidak valid';
  }

  static String? minLength(String? v, int min, {String label = 'Field'}) {
    if (v == null || v.length < min) return '$label minimal $min karakter';
    return null;
  }

  static String? phone(String? v) {
    if (v == null || v.trim().isEmpty) return 'Nomor wajib diisi';
    final RegExp re = RegExp(r'^[+\d][\d\s-]{7,15}$');
    return re.hasMatch(v.trim()) ? null : 'Format nomor tidak valid';
  }
}
