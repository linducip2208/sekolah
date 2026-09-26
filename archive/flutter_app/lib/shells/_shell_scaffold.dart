import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class ShellNavItem {
  const ShellNavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    required this.route,
  });
  final IconData icon;
  final IconData activeIcon;
  final String label;
  final String route;
}

class ShellScaffold extends StatelessWidget {
  const ShellScaffold({
    super.key,
    required this.child,
    required this.location,
    required this.items,
  });

  final Widget child;
  final String location;
  final List<ShellNavItem> items;

  int get _currentIndex {
    final int idx = items.indexWhere((ShellNavItem it) =>
        location == it.route || location.startsWith('${it.route}/'));
    return idx < 0 ? 0 : idx;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: child,
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (int i) => context.go(items[i].route),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: <NavigationDestination>[
          for (final ShellNavItem it in items)
            NavigationDestination(
              icon: Icon(it.icon),
              selectedIcon: Icon(it.activeIcon),
              label: it.label,
            ),
        ],
      ),
    );
  }
}
