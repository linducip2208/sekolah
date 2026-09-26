import 'package:flutter/material.dart';

import '../app/router/routes.dart';
import '_shell_scaffold.dart';

class AdminShell extends StatelessWidget {
  const AdminShell({super.key, required this.child, required this.location});
  final Widget child;
  final String location;

  static const List<ShellNavItem> _items = <ShellNavItem>[
    ShellNavItem(
      icon: Icons.dashboard_outlined,
      activeIcon: Icons.dashboard,
      label: 'Dashboard',
      route: Routes.adminDashboard,
    ),
    ShellNavItem(
      icon: Icons.person_add_outlined,
      activeIcon: Icons.person_add,
      label: 'Admisi',
      route: Routes.adminAdmissions,
    ),
    ShellNavItem(
      icon: Icons.receipt_long_outlined,
      activeIcon: Icons.receipt_long,
      label: 'Keuangan',
      route: Routes.adminFees,
    ),
    ShellNavItem(
      icon: Icons.campaign_outlined,
      activeIcon: Icons.campaign,
      label: 'Pengumuman',
      route: Routes.adminNotice,
    ),
    ShellNavItem(
      icon: Icons.person_outline,
      activeIcon: Icons.person,
      label: 'Profil',
      route: Routes.adminProfile,
    ),
  ];

  @override
  Widget build(BuildContext context) =>
      ShellScaffold(child: child, location: location, items: _items);
}
