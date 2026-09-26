import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../app/theme/app_colors.dart';
import '../../../../core/widgets/section_header.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/data/auth_repository.dart';
import '../../../../core/utils/validators.dart';

class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthBloc>().state.user;
    final school = context.watch<AuthBloc>().state.school;

    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        children: <Widget>[
          Container(
            padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
            color: Theme.of(context).colorScheme.surface,
            child: Row(
              children: <Widget>[
                CircleAvatar(
                  radius: 36,
                  backgroundColor: AppColors.primary.withValues(alpha: 0.12),
                  backgroundImage: user?.avatarUrl != null &&
                          user!.avatarUrl!.isNotEmpty
                      ? NetworkImage(user.avatarUrl!)
                      : null,
                  child: user?.avatarUrl == null || user!.avatarUrl!.isEmpty
                      ? const Icon(Icons.person, size: 36, color: AppColors.primary)
                      : null,
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Text(user?.name ?? '-',
                          style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 2),
                      Text(user?.email ?? '-',
                          style: Theme.of(context).textTheme.bodySmall),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          (user?.role ?? '').toUpperCase(),
                          style: const TextStyle(
                              color: AppColors.primary,
                              fontSize: 11,
                              fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SectionHeader(title: 'Sekolah'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              children: <Widget>[
                ListTile(
                  leading: const Icon(Icons.business_outlined),
                  title: Text(school?.name ?? '-'),
                  subtitle: Text(school?.code ?? ''),
                ),
                if (school?.address != null)
                  ListTile(
                    leading: const Icon(Icons.location_on_outlined),
                    title: Text(school!.address!),
                  ),
              ],
            ),
          ),
          const SectionHeader(title: 'Pengaturan'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              children: <Widget>[
                ListTile(
                  leading: const Icon(Icons.lock_outline),
                  title: const Text('Ubah kata sandi'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => _showChangePassword(context),
                ),
                ListTile(
                  leading: const Icon(Icons.language_outlined),
                  title: const Text('Bahasa'),
                  trailing: Text(_localeLabel(user?.locale ?? 'id')),
                  onTap: () => _showLocaleSheet(context),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: OutlinedButton.icon(
              icon: const Icon(Icons.logout, color: AppColors.danger),
              label: const Text('Keluar',
                  style: TextStyle(color: AppColors.danger)),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: AppColors.danger),
                minimumSize: const Size(double.infinity, 50),
              ),
              onPressed: () =>
                  context.read<AuthBloc>().add(const AuthLogoutRequested()),
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  String _localeLabel(String code) => switch (code) {
        'en' => 'English',
        'ar' => 'العربية',
        _ => 'Bahasa Indonesia',
      };

  void _showLocaleSheet(BuildContext context) {
    showModalBottomSheet<void>(
      context: context,
      builder: (BuildContext c) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <String>['id', 'en', 'ar']
              .map((String code) => ListTile(
                    title: Text(_localeLabel(code)),
                    onTap: () {
                      context.read<AuthBloc>().add(AuthLocaleChanged(code));
                      Navigator.of(c).pop();
                    },
                  ))
              .toList(),
        ),
      ),
    );
  }

  void _showChangePassword(BuildContext context) {
    final GlobalKey<FormState> key = GlobalKey<FormState>();
    final TextEditingController current = TextEditingController();
    final TextEditingController next = TextEditingController();
    bool saving = false;

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (BuildContext sheetCtx) => StatefulBuilder(
        builder: (BuildContext c, void Function(void Function()) setS) =>
            Padding(
          padding: EdgeInsets.fromLTRB(
            16, 16, 16,
            MediaQuery.of(c).viewInsets.bottom + 16,
          ),
          child: Form(
            key: key,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: <Widget>[
                Text('Ubah Kata Sandi',
                    style: Theme.of(c).textTheme.titleLarge),
                const SizedBox(height: 16),
                TextFormField(
                  controller: current,
                  obscureText: true,
                  decoration:
                      const InputDecoration(labelText: 'Kata sandi sekarang'),
                  validator: (String? v) =>
                      Validators.minLength(v, 6, label: 'Sandi'),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: next,
                  obscureText: true,
                  decoration:
                      const InputDecoration(labelText: 'Kata sandi baru'),
                  validator: (String? v) =>
                      Validators.minLength(v, 8, label: 'Sandi baru'),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: saving
                        ? null
                        : () async {
                            if (!key.currentState!.validate()) return;
                            setS(() => saving = true);
                            try {
                              await c.read<AuthRepository>().changePassword(
                                    current: current.text,
                                    next: next.text,
                                  );
                              if (!c.mounted) return;
                              Navigator.of(c).pop();
                              ScaffoldMessenger.of(c).showSnackBar(
                                const SnackBar(
                                    content: Text('Kata sandi berhasil diubah')),
                              );
                            } catch (e) {
                              if (!c.mounted) return;
                              ScaffoldMessenger.of(c).showSnackBar(
                                  SnackBar(content: Text(e.toString())));
                            } finally {
                              if (c.mounted) setS(() => saving = false);
                            }
                          },
                    child: saving
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor:
                                  AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Text('Simpan'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
