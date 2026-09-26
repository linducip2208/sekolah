import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import '../features/auth/presentation/bloc/auth_bloc.dart';
import '../l10n/app_localizations.dart';
import 'router/app_router.dart';
import 'theme/app_theme.dart';

class EschoolApp extends StatelessWidget {
  const EschoolApp({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      buildWhen: (AuthState p, AuthState c) =>
          p.status != c.status || p.user?.locale != c.user?.locale,
      builder: (BuildContext context, AuthState state) {
        final String localeCode = state.user?.locale ?? 'id';
        return MaterialApp.router(
          title: 'eSchool',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light(),
          darkTheme: AppTheme.dark(),
          themeMode: ThemeMode.system,
          routerConfig: AppRouter.of(context).config,
          locale: Locale(localeCode),
          supportedLocales: const <Locale>[
            Locale('id'),
            Locale('en'),
            Locale('ar'),
          ],
          localizationsDelegates: const <LocalizationsDelegate<dynamic>>[
            AppLocalizations.delegate,
            GlobalMaterialLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
          ],
        );
      },
    );
  }
}
