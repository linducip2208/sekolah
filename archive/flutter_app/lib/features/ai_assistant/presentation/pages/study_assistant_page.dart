import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../../../../core/api/api_client.dart';
import '../../../../core/api/api_endpoints.dart';

class _ChatMessage {
  final String role;
  final String content;
  _ChatMessage(this.role, this.content);
}

class StudyAssistantPage extends StatefulWidget {
  const StudyAssistantPage({super.key});

  @override
  State<StudyAssistantPage> createState() => _StudyAssistantPageState();
}

class _StudyAssistantPageState extends State<StudyAssistantPage> {
  final List<_ChatMessage> _messages = <_ChatMessage>[];
  final TextEditingController _ctrl = TextEditingController();
  final ScrollController _scroll = ScrollController();
  bool _loading = false;

  Future<void> _send() async {
    final String text = _ctrl.text.trim();
    if (text.isEmpty || _loading) return;

    setState(() {
      _messages.add(_ChatMessage('user', text));
      _loading = true;
      _ctrl.clear();
    });

    _scrollDown();

    try {
      final Response<dynamic> r = await ApiClient.dio.post<dynamic>(
        ApiEndpoints.aiStudyAssistant,
        data: <String, dynamic>{
          'messages': _messages.map((_ChatMessage m) => <String, String>{
                'role': m.role,
                'content': m.content,
              }).toList(),
          'temperature': 0.7,
          'max_tokens': 1024,
        },
      );
      final Map<String, dynamic> body = r.data is Map<String, dynamic>
          ? r.data as Map<String, dynamic>
          : <String, dynamic>{};
      final String reply = (body['text'] ?? '') as String;
      setState(() {
        _messages.add(_ChatMessage('assistant', reply));
      });
    } catch (e) {
      setState(() {
        _messages.add(_ChatMessage('assistant', '⚠️ Error: $e'));
      });
    } finally {
      if (mounted) setState(() => _loading = false);
      _scrollDown();
    }
  }

  void _scrollDown() {
    Future<void>.delayed(const Duration(milliseconds: 100), () {
      if (_scroll.hasClients) {
        _scroll.animateTo(
          _scroll.position.maxScrollExtent,
          duration: const Duration(milliseconds: 200),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('🤖 Study Assistant')),
      body: Column(children: [
        Expanded(
          child: _messages.isEmpty
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: Text(
                      'Tanyakan apa saja tentang materi pelajaranmu — matematika, IPA, sejarah, dll.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.grey),
                    ),
                  ),
                )
              : ListView.builder(
                  controller: _scroll,
                  padding: const EdgeInsets.all(12),
                  itemCount: _messages.length,
                  itemBuilder: (_, int i) {
                    final _ChatMessage m = _messages[i];
                    final bool isUser = m.role == 'user';
                    return Align(
                      alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
                      child: Container(
                        margin: const EdgeInsets.symmetric(vertical: 4),
                        padding: const EdgeInsets.all(12),
                        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.75),
                        decoration: BoxDecoration(
                          color: isUser ? Colors.blue.shade100 : Colors.grey.shade200,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: SelectableText(m.content),
                      ),
                    );
                  },
                ),
        ),
        if (_loading) const LinearProgressIndicator(),
        Container(
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            border: Border(top: BorderSide(color: Colors.grey.shade300)),
          ),
          padding: const EdgeInsets.all(8),
          child: SafeArea(
            top: false,
            child: Row(children: [
              Expanded(
                child: TextField(
                  controller: _ctrl,
                  decoration: const InputDecoration(
                    hintText: 'Tanya pelajaran...',
                    border: InputBorder.none,
                  ),
                  onSubmitted: (_) => _send(),
                  textInputAction: TextInputAction.send,
                ),
              ),
              IconButton(
                onPressed: _loading ? null : _send,
                icon: const Icon(Icons.send),
              ),
            ]),
          ),
        ),
      ]),
    );
  }
}
