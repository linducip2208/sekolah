import 'package:flutter/material.dart';

import '../app/router/routes.dart';
import '_shell_scaffold.dart';

class StudentShell extends StatelessWidget {
  const StudentShell({super.key, required this.child, required this.location});
  final Widget child;
  final String location;

  static const List<ShellNavItem> _items = <ShellNavItem>[
    ShellNavItem(
      icon: Icons.home_outlined,
      activeIcon: Icons.home,
      label: 'Beranda',
      route: Routes.studentDashboard,
    ),
    ShellNavItem(
      icon: Icons.schedule_outlined,
      activeIcon: Icons.schedule,
      label: 'Jadwal',
      route: Routes.studentTimetable,
    ),
    ShellNavItem(
      icon: Icons.menu_book_outlined,
      activeIcon: Icons.menu_book,
      label: 'Kelas',
      route: Routes.studentClassroom,
    ),
    ShellNavItem(
      icon: Icons.chat_bubble_outline,
      activeIcon: Icons.chat_bubble,
      label: 'Chat',
      route: Routes.studentChat,
    ),
    ShellNavItem(
      icon: Icons.person_outline,
      activeIcon: Icons.person,
      label: 'Profil',
      route: Routes.studentProfile,
    ),
  ];

  @override
  Widget build(BuildContext context) =>
      ShellScaffold(child: child, location: location, items: _items);
}
