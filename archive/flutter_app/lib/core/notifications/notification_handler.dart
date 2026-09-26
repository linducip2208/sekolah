import 'package:firebase_messaging/firebase_messaging.dart';

import '../../app/router/app_router.dart';

class NotificationHandler {
  NotificationHandler._();

  static void handleMessage(RemoteMessage message) {
    _route(message.data);
  }

  static void handlePayload(String raw) {
    final Map<String, String> data = <String, String>{};
    for (final String part in raw.split('&')) {
      final List<String> kv = part.split('=');
      if (kv.length == 2) data[kv[0]] = kv[1];
    }
    _route(data);
  }

  static void _route(Map<String, dynamic> data) {
    final dynamic type = data['type'];
    final dynamic id = data['id'] ?? data['ref_id'];
    final routerCfg = AppRouter.maybeInstance?.router;
    if (routerCfg == null) return;

    final String path = switch (type) {
      'attendance' => '/student/attendance',
      'fee' || 'invoice' => '/student/fees',
      'assignment' || 'classroom' => '/student/classroom',
      'exam' => '/student/exam',
      'marks' => '/student/marks',
      'chat' when id != null => '/chat/$id',
      'notice' => '/notice',
      _ => '/notifications',
    };
    routerCfg.push(path);
  }
}
