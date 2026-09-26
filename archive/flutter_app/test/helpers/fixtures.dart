import 'package:eschool_app/features/auth/data/auth_repository.dart';
import 'package:eschool_app/features/auth/data/models/school_model.dart';
import 'package:eschool_app/features/auth/data/models/user_model.dart';

const UserModel testUser = UserModel(
  id: 1,
  schoolId: 1,
  name: 'Budi Sanjaya',
  email: 'budi@school.id',
  role: 'student',
  locale: 'id',
);

const SchoolModel testSchool = SchoolModel(
  id: 1,
  name: 'SMP Nusantara',
  subdomain: 'smp-nusantara',
  timezone: 'Asia/Jakarta',
  locale: 'id',
);

final AuthSession testSession = AuthSession(
  user: testUser,
  school: testSchool,
  token: 'fake-token',
);
