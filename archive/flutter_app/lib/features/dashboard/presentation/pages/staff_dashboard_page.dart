import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../widgets/greeting_header.dart';

class StaffDashboardPage extends StatelessWidget {
  const StaffDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthBloc>().state.user;
    final school = context.watch<AuthBloc>().state.school;

    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: ListView(
          children: <Widget>[
            GreetingHeader(
              name: user?.name ?? 'Staf',
              subtitle: '${school?.name ?? ''} • ${user?.role ?? ''}',
              avatarUrl: user?.avatarUrl,
            ),
            const Padding(
              padding: EdgeInsets.all(24),
              child: Card(
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: Column(
                    children: <Widget>[
                      Icon(Icons.dashboard_customize_outlined, size: 56),
                      SizedBox(height: 12),
                      Text(
                        'Modul khusus akan muncul sesuai peran Anda.',
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
