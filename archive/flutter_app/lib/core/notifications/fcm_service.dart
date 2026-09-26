import 'package:dio/dio.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import '../api/api_client.dart';
import '../api/api_endpoints.dart';
import '../storage/app_storage.dart';
import 'notification_handler.dart';

class FcmService {
  FcmService._();
  static final FcmService instance = FcmService._();

  final FirebaseMessaging _fcm = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _local =
      FlutterLocalNotificationsPlugin();

  static const AndroidNotificationChannel _channel = AndroidNotificationChannel(
    'eschool_default',
    'eSchool Notifications',
    description: 'Default channel untuk notifikasi eSchool',
    importance: Importance.high,
  );

  bool _initialized = false;

  Future<void> init() async {
    if (_initialized) return;
    _initialized = true;

    await _fcm.requestPermission(alert: true, badge: true, sound: true);

    const AndroidInitializationSettings androidInit =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const DarwinInitializationSettings iosInit = DarwinInitializationSettings();
    await _local.initialize(
      const InitializationSettings(android: androidInit, iOS: iosInit),
      onDidReceiveNotificationResponse: (NotificationResponse r) {
        if (r.payload != null) {
          NotificationHandler.handlePayload(r.payload!);
        }
      },
    );

    await _local
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);

    FirebaseMessaging.onMessage.listen(_onForegroundMessage);
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage m) {
      NotificationHandler.handleMessage(m);
    });

    final RemoteMessage? initial = await _fcm.getInitialMessage();
    if (initial != null) {
      NotificationHandler.handleMessage(initial);
    }

    await _refreshToken();
    _fcm.onTokenRefresh.listen((String t) async {
      await AppStorage.saveFcmToken(t);
      await _registerToBackend(t);
    });
  }

  Future<void> _refreshToken() async {
    try {
      final String? t = await _fcm.getToken();
      if (t == null) return;
      await AppStorage.saveFcmToken(t);
      await _registerToBackend(t);
    } catch (e) {
      if (kDebugMode) debugPrint('[FCM] token error: $e');
    }
  }

  Future<void> _registerToBackend(String token) async {
    final String? jwt = await AppStorage.getToken();
    if (jwt == null) return;
    try {
      await ApiClient.dio.post<dynamic>(
        ApiEndpoints.registerFcmToken,
        data: <String, String>{'token': token, 'platform': 'mobile'},
      );
    } on DioException catch (e) {
      if (kDebugMode) debugPrint('[FCM] register backend error: $e');
    }
  }

  Future<void> _onForegroundMessage(RemoteMessage m) async {
    final RemoteNotification? n = m.notification;
    if (n == null) return;

    final String payload = m.data.entries
        .map((MapEntry<String, dynamic> e) => '${e.key}=${e.value}')
        .join('&');

    await _local.show(
      n.hashCode,
      n.title,
      n.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _channel.id,
          _channel.name,
          channelDescription: _channel.description,
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
        iOS: const DarwinNotificationDetails(),
      ),
      payload: payload,
    );
  }
}
