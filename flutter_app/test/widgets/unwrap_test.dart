import 'package:eschool_app/core/api/response_unwrap.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  group('response_unwrap', () {
    test('unwrap returns data key when present', () {
      final dynamic input = <String, dynamic>{
        'data': <String, int>{'a': 1}
      };
      expect(unwrap(input), <String, int>{'a': 1});
    });

    test('unwrap returns body itself when no data key', () {
      expect(unwrap(<String, int>{'a': 1}), <String, int>{'a': 1});
      expect(unwrap(<int>[1, 2, 3]), <int>[1, 2, 3]);
    });

    test('unwrapList parses arrays of maps', () {
      final List<Map<String, dynamic>> got = unwrapList(<Map<String, int>>[
        <String, int>{'id': 1},
        <String, int>{'id': 2},
      ]);
      expect(got, hasLength(2));
      expect(got.first['id'], 1);
    });

    test('unwrapList returns empty for non-list payload', () {
      expect(unwrapList(<String, dynamic>{'foo': 'bar'}), isEmpty);
    });

    test('unwrapMap returns empty map for non-map payload', () {
      expect(unwrapMap(<int>[1, 2, 3]), isEmpty);
    });
  });
}
