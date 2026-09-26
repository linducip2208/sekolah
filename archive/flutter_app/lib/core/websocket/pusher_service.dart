import 'package:flutter/foundation.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

import '../config/app_config.dart';
import '../storage/app_storage.dart';

typedef PusherEventHandler = void Function(PusherEvent event);

class PusherService {
  PusherService._();
  static final PusherService instance = PusherService._();

  PusherChannelsFlutter? _client;
  bool _connected = false;
  final Map<String, PusherEventHandler> _handlers = <String, PusherEventHandler>{};

  bool get isConnected => _connected;

  Future<void> connect() async {
    if (_connected) return;
    final String? jwt = await AppStorage.getToken();
    if (jwt == null) return;

    _client = PusherChannelsFlutter.getInstance();
    await _client!.init(
      apiKey: AppConfig.pusherKey,
      cluster: AppConfig.pusherCluster,
      onEvent: _onEvent,
      onError: (String message, int? code, dynamic e) {
        if (kDebugMode) debugPrint('[Pusher] error $code: $message');
      },
      onConnectionStateChange: (dynamic prev, dynamic next) {
        _connected = (next.toString()).toUpperCase().contains('CONNECTED');
      },
      authEndpoint: '${AppConfig.apiBaseUrl}/broadcasting/auth',
      authParams: <String, dynamic>{
        'headers': <String, String>{'Authorization': 'Bearer $jwt'},
      },
    );

    await _client!.connect();
    _connected = true;
  }

  Future<void> subscribe(String channelName, PusherEventHandler onEvent) async {
    await connect();
    _handlers[channelName] = onEvent;
    await _client!.subscribe(channelName: channelName);
  }

  Future<void> unsubscribe(String channelName) async {
    _handlers.remove(channelName);
    await _client?.unsubscribe(channelName: channelName);
  }

  void _onEvent(PusherEvent event) {
    final PusherEventHandler? handler = _handlers[event.channelName];
    handler?.call(event);
  }

  Future<void> disconnect() async {
    await _client?.disconnect();
    _connected = false;
  }
}
