import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/utils/date_formatter.dart';
import '../../../../core/websocket/pusher_service.dart';
import '../../../../core/widgets/app_error.dart';
import '../../../../core/widgets/app_loading.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../data/chat_repository.dart';

class ChatConversationPage extends StatefulWidget {
  const ChatConversationPage({super.key, required this.conversationId});
  final int conversationId;

  @override
  State<ChatConversationPage> createState() => _ChatConversationPageState();
}

class _ChatConversationPageState extends State<ChatConversationPage> {
  final ChatRepository _repo = ChatRepository();
  final TextEditingController _input = TextEditingController();
  final ScrollController _scroll = ScrollController();
  late Future<List<Map<String, dynamic>>> _future;
  final List<Map<String, dynamic>> _live = <Map<String, dynamic>>[];
  bool _sending = false;

  String get _channel => 'private-conversation.${widget.conversationId}';

  @override
  void initState() {
    super.initState();
    _future = _repo.messages(widget.conversationId);
    _subscribe();
  }

  Future<void> _subscribe() async {
    try {
      await PusherService.instance.subscribe(_channel, _onPusherEvent);
    } catch (_) {}
  }

  void _onPusherEvent(PusherEvent event) {
    if (event.eventName != 'message.new') return;
    try {
      final Map<String, dynamic> data =
          json.decode(event.data) as Map<String, dynamic>;
      setState(() => _live.add(data));
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (_scroll.hasClients) {
          _scroll.animateTo(
            _scroll.position.maxScrollExtent + 80,
            duration: const Duration(milliseconds: 200),
            curve: Curves.easeOut,
          );
        }
      });
    } catch (_) {}
  }

  Future<void> _send() async {
    final String text = _input.text.trim();
    if (text.isEmpty) return;
    setState(() => _sending = true);
    try {
      final Map<String, dynamic> sent =
          await _repo.send(widget.conversationId, text);
      _input.clear();
      setState(() => _live.add(sent));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  void dispose() {
    PusherService.instance.unsubscribe(_channel);
    _input.dispose();
    _scroll.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final int? meId = context.watch<AuthBloc>().state.user?.id;
    return Scaffold(
      appBar: AppBar(title: const Text('Pesan')),
      body: Column(
        children: <Widget>[
          Expanded(
            child: FutureBuilder<List<Map<String, dynamic>>>(
              future: _future,
              builder: (BuildContext c,
                  AsyncSnapshot<List<Map<String, dynamic>>> snap) {
                if (snap.connectionState == ConnectionState.waiting) {
                  return const AppLoading();
                }
                if (snap.hasError) {
                  return AppError(
                      message: '${snap.error}',
                      onRetry: () => setState(
                          () => _future = _repo.messages(widget.conversationId)));
                }
                final List<Map<String, dynamic>> all =
                    <Map<String, dynamic>>[...?snap.data, ..._live];
                if (all.isEmpty) {
                  return const AppEmpty(title: 'Belum ada pesan');
                }
                return ListView.builder(
                  controller: _scroll,
                  padding: const EdgeInsets.all(12),
                  itemCount: all.length,
                  itemBuilder: (BuildContext c, int i) {
                    final Map<String, dynamic> m = all[i];
                    final bool mine = (m['user_id'] as num?)?.toInt() == meId;
                    final Color bg = mine
                        ? AppColors.primary
                        : Theme.of(context).colorScheme.surfaceContainerHighest;
                    return Padding(
                      padding: const EdgeInsets.symmetric(vertical: 4),
                      child: Row(
                        mainAxisAlignment: mine
                            ? MainAxisAlignment.end
                            : MainAxisAlignment.start,
                        children: <Widget>[
                          Flexible(
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: bg,
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: <Widget>[
                                  Text(m['body'] as String? ?? '',
                                      style: TextStyle(
                                        color:
                                            mine ? Colors.white : null,
                                      )),
                                  const SizedBox(height: 2),
                                  if (m['created_at'] != null)
                                    Text(
                                      DateFormatter.time(DateTime.parse(
                                          m['created_at'] as String)),
                                      style: TextStyle(
                                        fontSize: 10,
                                        color: mine
                                            ? Colors.white70
                                            : Theme.of(context).hintColor,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                );
              },
            ),
          ),
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 8, 8),
              child: Row(
                children: <Widget>[
                  Expanded(
                    child: TextField(
                      controller: _input,
                      decoration: const InputDecoration(
                        hintText: 'Tulis pesan...',
                      ),
                      minLines: 1,
                      maxLines: 4,
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton.filled(
                    icon: _sending
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor:
                                  AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Icon(Icons.send),
                    onPressed: _sending ? null : _send,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
