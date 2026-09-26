/// Defensive unwrap: backend kadang membungkus dengan { "data": ... },
/// kadang langsung mengembalikan raw payload. Helper ini menormalisasi.
dynamic unwrap(dynamic body) {
  if (body is Map && body.containsKey('data')) return body['data'];
  return body;
}

List<Map<String, dynamic>> unwrapList(dynamic body) {
  final dynamic raw = unwrap(body);
  if (raw is List) {
    return raw
        .map((dynamic e) => Map<String, dynamic>.from(e as Map))
        .toList();
  }
  return const <Map<String, dynamic>>[];
}

Map<String, dynamic> unwrapMap(dynamic body) {
  final dynamic raw = unwrap(body);
  if (raw is Map) return Map<String, dynamic>.from(raw);
  return const <String, dynamic>{};
}
