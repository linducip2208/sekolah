import 'package:flutter/material.dart';

import '../app/router/routes.dart';
import '_shell_scaffold.dart';

class StaffShell extends StatelessWidget {
  const StaffShell({super.key, required this.child, required this.location});
  final Widget child;
  final String location;

  static const List<ShellNavItem> _items = <ShellNavItem>[
    ShellNavItem(
      icon: Icons.home_outlined,
      activeIcon: Icons.home,
      label: 'Beranda',
      route: Routes.staffDashboard,
    ),
    ShellNavItem(
      icon: Icons.person_outline,
      activeIcon: Icons.person,
      label: 'Profil',
      route: Routes.staffProfile,
    ),
  ];

  @override
  Widget build(BuildContext context) =>
      ShellScaffold(child: child, location: location, items: _items);
}
