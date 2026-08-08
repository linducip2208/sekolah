# Entity Relationship Diagram — Sikad Pro

## Tabel Utama & Relasi

```
PLATFORM (no school_id)
┌──────────┐     ┌──────────┐
│  plans   │────<│  schools │
└──────────┘     └────┬─────┘
                      │ 1
                      │
                      │ N
┌──────────┐     ┌────▼─────┐
│  roles   │────<│  users   │
└──────────┘     └────┬─────┘
                      │ 1 school_id
                      │
    ┌─────────────────┼─────────────────────┐
    │                 │                     │
    │ N               │ N                   │ N
    ▼                 ▼                     ▼
┌─────────┐   ┌─────────────┐    ┌──────────────────┐
│students │   │   staffs    │    │ (parent via       │
└────┬────┘   └──────┬──────┘    │  parent_student   │
     │               │           │  pivot)           │
     │               │           └──────────────────┘
```

---

## Schema Lengkap Per Domain

### Foundation

```
plans
  id, name, slug, price(int), max_students, max_teachers, features(json), is_active

schools
  id, name, subdomain(unique), logo, address, phone, email,
  timezone, locale, is_active, plan_id(→plans), plan_expires_at,
  settings(json), created_at, updated_at, deleted_at

school_branding              ← Module 03b (whitelabel per school)
  id, school_id(unique→schools), display_name, tagline, school_type_label,
  academic_year_format,
  logo_primary_path, logo_secondary_path, logo_monochrome_path, favicon_path,
  color_primary, color_secondary, color_success, color_warning, color_danger,
  background_mode(light|dark|auto),
  login_background_path, login_welcome_text(json), login_show_motto,
  mobile_splash_logo_path, mobile_splash_bg_color, mobile_app_display_name,
  email_header_logo_path, email_footer_text,
  receipt_layout(simple|formal|modern), pdf_watermark_enabled,
  fcm_notification_icon_path, fcm_notification_color,
  cache_version

users
  id, school_id(→schools nullable), name, email(unique), phone, avatar,
  email_verified_at, password, fcm_token, locale, is_active,
  remember_token, created_at, updated_at, deleted_at

roles, permissions, model_has_roles, model_has_permissions  ← Spatie
personal_access_tokens  ← Sanctum
```

---

### Academic Core

```
academic_years
  id, school_id, name, start_date, end_date, is_active
  UNIQUE(school_id, name)

semesters
  id, school_id, academic_year_id(→academic_years), name,
  start_date, end_date, is_active

school_holidays
  id, school_id, title, date, end_date, type

mediums
  id, school_id, name
  UNIQUE(school_id, name)

class_rooms
  id, school_id, medium_id(→mediums), name, order
  UNIQUE(school_id, name)

sections
  id, school_id, name
  UNIQUE(school_id, name)

subjects
  id, school_id, name, code, type, bg_color
  UNIQUE(school_id, name)

class_sections
  id, school_id, academic_year_id, class_room_id(→class_rooms),
  section_id(→sections), class_teacher_id(→users), capacity
  UNIQUE(school_id, academic_year_id, class_room_id, section_id)

class_section_subjects
  id, school_id, class_section_id, subject_id, teacher_id(→users)
  UNIQUE(school_id, class_section_id, subject_id)

students
  id, school_id, user_id(→users), class_section_id, academic_year_id,
  admission_no, roll_number, admission_date, date_of_birth, gender,
  blood_group, religion, address, city, state, zip_code,
  guardian_name, guardian_phone, guardian_email, guardian_relation,
  has_transport, has_hostel
  UNIQUE(school_id, admission_no)

parent_student (pivot)
  id, parent_id(→users), student_id(→students), relation
  UNIQUE(parent_id, student_id)

staffs
  id, school_id, user_id(→users), staff_id, designation, department,
  qualification, experience, joining_date, date_of_birth, gender,
  blood_group, address, emergency_contact, basic_salary(int)
  UNIQUE(school_id, staff_id)
```

---

### Attendance

```
attendances
  id, school_id, student_id, class_section_id, marked_by(→users),
  date, status(present|absent|late|half_day|on_leave), note
  UNIQUE(school_id, student_id, date)
  INDEX(school_id, class_section_id, date)
```

---

### Timetable

```
timetables
  id, school_id, academic_year_id, class_section_id, subject_id,
  teacher_id, day_of_week(1-7), start_time, end_time, room
  UNIQUE(school_id, class_section_id, day_of_week, start_time)
  INDEX(school_id, teacher_id, day_of_week)

timetable_breaks
  id, school_id, name, day_of_week(0=all), start_time, end_time
```

---

### Online Classroom

```
lessons
  id, school_id, class_section_id, subject_id, teacher_id, name, description, date

lesson_topics
  id, school_id, lesson_id, name, description(text), file(s3),
  file_thumbnail, video_url, order

study_materials
  id, school_id, class_section_id, subject_id, teacher_id,
  title, description, file(s3), file_type, file_size

assignments
  id, school_id, class_section_id, subject_id, teacher_id,
  title, instructions, file(s3), due_date, total_marks, extra_marks_allowed

assignment_submissions
  id, school_id, assignment_id, student_id, file(s3), notes,
  submitted_at, is_late, status(pending|submitted|graded|returned),
  marks, teacher_feedback, graded_by(→users), graded_at
  UNIQUE(school_id, assignment_id, student_id)
```

---

### Exam Engine

```
exams
  id, school_id, class_section_id, subject_id, teacher_id, semester_id,
  title, instructions, type(online|offline), start_datetime, end_datetime,
  duration_minutes, total_marks, passing_marks,
  shuffle_questions, shuffle_options, show_result_immediately,
  status(draft|published|active|completed)

exam_questions
  id, school_id, exam_id, question, type(mcq|true_false|essay),
  marks, explanation, order, image(s3)

exam_question_options
  id, exam_question_id, option_text, option_image, is_correct, order

exam_submissions
  id, school_id, exam_id, student_id, started_at, submitted_at,
  obtained_marks, status(not_started|in_progress|submitted|graded), is_passed
  UNIQUE(school_id, exam_id, student_id)

exam_answers
  id, school_id, exam_submission_id, exam_question_id,
  selected_option_id(→exam_question_options), essay_answer,
  marks_awarded, teacher_comment
  UNIQUE(exam_submission_id, exam_question_id)
```

---

### Marks & Grades

```
grade_systems
  id, school_id, name, is_active

grade_rules
  id, grade_system_id, grade, min_percentage, max_percentage, description, gpa_value

marks
  id, school_id, student_id, exam_id, subject_id, class_section_id, semester_id,
  obtained_marks, total_marks, percentage(virtual), grade, teacher_remarks
  UNIQUE(school_id, student_id, exam_id, subject_id)

report_cards
  id, school_id, student_id, class_section_id, semester_id,
  subject_results(json), total_percentage, gpa, overall_grade,
  rank, class_size, is_passed, class_teacher_remarks, principal_remarks,
  is_published, published_at
  UNIQUE(school_id, student_id, semester_id)
```

---

### Admission

```
admission_inquiries
  id, school_id, student_name, parent_name, phone, email, class_room_id,
  source, message, status(new|contacted|converted|dropped), follow_up_at

admission_forms
  id, school_id, academic_year_id, class_room_id, admission_no,
  student_name, date_of_birth, gender, blood_group, religion,
  address, city, state, zip_code, previous_school, previous_class,
  photo(s3), birth_certificate(s3), previous_marksheet(s3),
  guardian_name, guardian_relation, guardian_phone, guardian_email,
  guardian_occupation, guardian_photo(s3),
  status(pending|under_review|approved|rejected|enrolled),
  rejection_reason, reviewed_by(→users), reviewed_at
  UNIQUE(school_id, admission_no)
```

---

### Finance

```
fee_categories
  id, school_id, name, description, is_active
  UNIQUE(school_id, name)

fee_structures
  id, school_id, fee_category_id, class_room_id(nullable), academic_year_id,
  name, amount(int), frequency(monthly|yearly|one_time|semester), due_date,
  late_fee(int), late_fee_per_day(int), is_active

fee_discounts
  id, school_id, name, type(percentage|fixed), value(int)

fee_invoices
  id, school_id, student_id, fee_structure_id, invoice_no, invoice_date,
  due_date, amount(int), discount_amount(int), fine_amount(int), net_amount(int),
  status(unpaid|partial|paid|waived), month, note
  UNIQUE(school_id, student_id, fee_structure_id, month)

fee_payments
  id, school_id, fee_invoice_id, collected_by(→users), amount(int),
  payment_date, payment_mode, transaction_id, receipt_no, note

payment_providers           ← Module 11b (dynamic, format-based)
  id, school_id, name, slug, api_format(redirect_checkout|virtual_account|
    ewallet_deeplink|qris_dynamic|qris_static|bank_transfer_manual|cash),
  base_url, api_key_encrypted, secret_key_encrypted, merchant_id_encrypted,
  webhook_secret_encrypted, callback_url, extra_config(json), extra_headers(json),
  is_sandbox, is_active, priority
  UNIQUE(school_id, slug)
  INDEX(school_id, is_active, api_format)

payment_methods
  id, school_id, payment_provider_id(→payment_providers), code, display_name,
  logo_url, instruction_template, fee_flat(int), fee_percent_bp,
  fee_borne_by(0=parent|1=school), min_amount, max_amount, expiry_minutes,
  is_active, sort_order
  UNIQUE(school_id, code)

payment_transactions
  id, school_id, fee_invoice_id(→fee_invoices), payment_method_id,
  payment_provider_id, initiated_by(→users), fee_payment_id(nullable→fee_payments),
  reference_no(unique), external_id, gateway_transaction_id,
  amount(int), fee_amount(int), net_amount(int), currency,
  status(pending|awaiting_payment|paid|expired|failed|cancelled|refunded|disputed),
  redirect_url, va_number, va_bank_code, qr_string, deeplink_url,
  raw_request(json), raw_response(json), expired_at, paid_at
  INDEX(school_id, fee_invoice_id, status)

payment_webhook_logs
  id, payment_provider_id, payment_transaction_id, source_ip, headers(json),
  payload(json), signature_status, processing_status, error_message

payroll_allowances
  id, school_id, title, type(fixed|percentage), amount(int)

payroll_deductions
  id, school_id, title, type(fixed|percentage), amount(int)

staff_payroll_structures
  id, school_id, staff_id(→staffs), basic_salary(int),
  allowances(json), deductions(json), is_active
  UNIQUE(school_id, staff_id)

payroll_slips
  id, school_id, staff_id, month, basic_salary(int), allowances(json),
  deductions(json), total_allowances(int), total_deductions(int), net_salary(int),
  working_days, present_days, attendance_deduction(int),
  status(draft|paid|cancelled), paid_date, payment_mode, note, generated_by(→users)
  UNIQUE(school_id, staff_id, month)

subscription_transactions
  id, school_id, plan_id, transaction_no(unique), amount(int),
  payment_mode, reference, months, start_date, end_date,
  status(pending|completed|failed|refunded), note, processed_by(→users)
```

---

### Facilities

```
book_categories
  id, school_id, name
  UNIQUE(school_id, name)

books
  id, school_id, book_category_id, title, author, isbn, publisher,
  publish_year, edition, total_quantity, available_quantity, cover(s3),
  barcode, description, rack_location, is_active
  INDEX(school_id, title), INDEX(school_id, isbn), INDEX(school_id, barcode)

book_issues
  id, school_id, book_id, issued_to(→users), issued_by(→users),
  returned_to(→users nullable), issue_date, due_date, return_date,
  status(issued|returned|overdue|lost), fine_amount(int), fine_paid

hostel_blocks
  id, school_id, name, gender(male|female|mixed), warden_name, warden_phone, is_active

hostel_rooms
  id, school_id, hostel_block_id, room_no, type(single|sharing|dormitory),
  capacity, fee_per_month(int), status(available|full|maintenance)
  UNIQUE(school_id, hostel_block_id, room_no)

hostel_allocations
  id, school_id, hostel_room_id, student_id, check_in_date, check_out_date,
  status(active|checked_out|transferred), note, allocated_by(→users)
  UNIQUE(school_id, student_id, status=active)

transport_routes
  id, school_id, title, start_point, end_point, pickup_time, drop_time,
  fee_per_month(int), distance_km, is_active

transport_route_stops
  id, transport_route_id, stop_name, pickup_time, order

vehicles
  id, school_id, name, registration_no, type(bus|minibus|van), capacity,
  driver_name, driver_phone, driver_license, insurance_expiry, is_active
  UNIQUE(school_id, registration_no)

route_vehicles (pivot)
  id, transport_route_id, vehicle_id
  UNIQUE(transport_route_id, vehicle_id)

student_transports
  id, school_id, student_id, transport_route_id, transport_route_stop_id,
  start_date, end_date, is_active
  UNIQUE(school_id, student_id, is_active=true)
```

---

### Communication

```
notices
  id, school_id, created_by(→users), title, description, attachment(s3),
  attachment_type, target_roles(json nullable), target_class_sections(json nullable),
  publish_at, expire_at, is_published, send_notification

notice_reads
  id, notice_id, user_id, read_at
  UNIQUE(notice_id, user_id)

conversations
  id, school_id, user_one(→users), user_two(→users),
  last_message_id, last_message_at
  UNIQUE(school_id, user_one, user_two)

messages
  id, school_id, conversation_id, sender_id(→users), message,
  attachment(s3), attachment_type, attachment_name,
  type(text|image|file|audio), is_read, read_at

notifications
  id, school_id, user_id(→users), sent_by(→users nullable), title, body,
  type, data(json), is_read, read_at
  INDEX(school_id, user_id, is_read)
```

---

## Phase 8+ — Best-in-Class Modules (Modules 22–45)

### PPDB (Module 22)

```
ppdb_periods
  id, school_id, academic_year_id, name, open_date, close_date, announcement_date,
  reregistration_deadline, form_fee(int), jalur_config(json), document_requirements(json),
  is_published

ppdb_applications
  id, school_id, ppdb_period_id, registration_no(unique), jalur,
  student_name, nisn, date_of_birth, gender, address, district, city, home_lat, home_lng,
  distance_km, previous_school, parent_name, parent_phone, parent_email,
  documents(json), achievements(json), average_score, ranking_score, rank_position,
  status(draft|submitted|verified|accepted|rejected|enrolled|withdrew),
  reviewer_id(→users), reviewer_note, form_payment_id(→fee_payments),
  submitted_at, verified_at, accepted_at

ppdb_zonasi_zones
  id, school_id, district, subdistrict, priority_score
```

### Bus Tracking + ID Gate (Module 23)

```
vehicle_locations
  id, school_id, vehicle_id, lat, lng, speed_kmh, heading_deg, recorded_at

vehicle_trips
  id, school_id, vehicle_id, transport_route_id, direction(pickup|drop),
  started_at, ended_at, stops_completed(json), status

id_gate_devices
  id, school_id, name, location, device_token_encrypted, type(entry|exit|both), is_active

id_gate_events
  id, school_id, id_gate_device_id, user_id, direction(in|out), scanned_at

student_id_cards
  id, school_id, student_id(unique), card_uid(unique), qr_token(unique), is_active, issued_at
```

### UKS / Klinik (Module 24)

```
medical_records
  id, school_id, student_id(unique), blood_type, allergies(json), chronic_conditions(json),
  current_medications(json), emergency_contact_name, emergency_contact_phone,
  insurance_provider, insurance_number

clinic_visits
  id, school_id, student_id, attended_by(→users), visit_at, symptoms, diagnosis,
  treatment, medications_given(json), temperature_c, blood_pressure,
  parent_notified, returned_to_class, sent_home, referred_external, referred_to

vaccinations
  id, school_id, student_id, vaccine_name, vaccinated_at, batch_number,
  administered_by, next_dose_due, certificate_path
```

### BP/BK + Discipline (Module 25)

```
counseling_sessions
  id, school_id, student_id, counselor_id(→users), scheduled_at, duration_minutes,
  type(academic|behavior|mental_health|career|family|social),
  status, notes, refer_external, referred_to

discipline_categories
  id, school_id, name, type(violation|achievement), point_value, description,
  auto_sanction, sanction_thresholds(json)

discipline_records
  id, school_id, student_id, discipline_category_id, reported_by(→users),
  incident_date, description, evidence_files(json), points,
  status(reported|reviewed|sanctioned|closed), sanction_applied, parent_notified

bullying_reports
  id, school_id, reporter_id(→users nullable), is_anonymous,
  victims_described(json), perpetrators_described(json),
  type(verbal|physical|cyber|social|other), incident_date, location,
  description, evidence_files(json),
  status(received|investigating|action_taken|closed|unfounded),
  assigned_to(→users), investigation_notes, action_summary

wellness_checkins
  id, school_id, student_id, checkin_date, mood_score(1-10),
  feeling_tags(json), note, flagged_for_review
  UNIQUE(student_id, checkin_date)
```

### Lesson Plan / RPP (Module 26)

```
lesson_plans
  id, school_id, class_section_id, subject_id, teacher_id, semester_id,
  title, lesson_date, duration_minutes,
  learning_objectives(json), material_summary, activities(json),
  assessment_methods(json), resources(json),
  curriculum_type, status(draft|submitted|approved|rejected|completed),
  reviewer_id(→users), reviewed_at, reviewer_feedback,
  actually_executed, execution_note

lesson_plan_attachments
  id, school_id, lesson_plan_id, file_path, file_name, mime, size_bytes
```

### Cafeteria / Kantin (Module 27)

```
canteen_wallets
  id, school_id, student_id(unique), balance(int), daily_limit, blocked_categories(json), is_locked

canteen_topups
  id, school_id, canteen_wallet_id, initiated_by(→users), payment_transaction_id,
  amount, status(pending|completed|failed|refunded)

canteen_categories
  id, school_id, name, icon, healthy_tag

canteen_menu_items
  id, school_id, canteen_category_id, name, description, price(int), photo_path,
  allergens(json), is_available, stock_today

canteen_orders
  id, school_id, student_id, canteen_wallet_id, order_no(unique),
  pickup_at, items(json), total, source(preorder|walkin),
  status(pending|preparing|ready|picked_up|cancelled)
```

### Pesantren / Madrasah Mode (Module 28)

```
religious_mode_config
  id, school_id(unique), enabled, religion(islam|christian|catholic|hindu|buddha|confucian),
  institution_type, hijri_holidays(json), use_hijri_calendar, prayer_times_config(json)

hafalan_targets
  id, school_id, class_section_id, name, target_ranges(json), start_date, deadline

hafalan_progress
  id, school_id, student_id, hafalan_target_id, verified_by(→users),
  surah, ayah_start, ayah_end, memorized_at, quality, note, audio_path(json)

ibadah_logs
  id, school_id, student_id, log_date,
  subuh, dzuhur, ashar, maghrib, isya (each: done|late|missed|jamaah),
  puasa_sunnah, tilawah_done, tilawah_ayah_count,
  extra_amalan(json), verified_by(→users)
  UNIQUE(student_id, log_date)

kitab_kuning_progress
  id, school_id, student_id, teacher_id(→users), kitab_name,
  current_bab, halaman_terakhir, last_session, catatan_ustadz
```

### Donations + Alumni (Modules 29, 30)

```
donation_campaigns
  id, school_id, title, slug, description, target_amount(int), raised_amount(int),
  start_date, end_date, cover_image_path, updates(json),
  category(scholarship|building|equipment|emergency|general),
  status(draft|active|completed|cancelled), is_public

donations
  id, school_id, donation_campaign_id, user_id, donor_name, donor_email, donor_phone,
  npwp, is_anonymous, show_amount, amount, message, payment_transaction_id,
  status(pending|completed|failed|refunded), receipt_no(unique), donated_at

alumni_profiles
  id, school_id, user_id(unique), graduation_year, class_of, current_position,
  current_company, city, country, linkedin_url, industry, skills(json),
  willing_to_mentor, willing_to_offer_internship, verified

alumni_job_posts
  id, school_id, posted_by(→users), title, company, location, type, description,
  apply_url, expires_at, is_active

alumni_mentorships
  id, school_id, mentor_id(→users), mentee_id(→users),
  status(requested|active|completed|cancelled), goals, start_date, end_date

alumni_events
  id, school_id, title, description, starts_at, ends_at, venue, city,
  capacity, ticket_price(int), is_published
```

### AI Assistant (Module 31, dynamic provider)

```
ai_providers
  id, school_id, name, slug, api_format(openai_compatible|anthropic_format|gemini_format|image_generic),
  base_url, api_key_encrypted, extra_headers(json), extra_config(json),
  is_active, priority

ai_models
  id, school_id, ai_provider_id, model_name(user input), display_name,
  capability(chat|completion|embedding|image_gen|image_analysis|speech_to_text|tts),
  context_window, input_price_per_1k, output_price_per_1k, is_active

ai_feature_assignments
  id, school_id, feature_key, ai_model_id, feature_config(json), is_enabled
  UNIQUE(school_id, feature_key)

ai_usage_logs
  id, school_id, user_id, ai_model_id, feature_key, input_tokens, output_tokens,
  estimated_cost, latency_ms, success, error
```

### Dapodik (Module 32)

```
dapodik_config
  id, school_id(unique), npsn, username_encrypted, password_encrypted,
  endpoint_url, field_mappings(json), last_sync_at

dapodik_sync_logs
  id, school_id, direction(import|export), entity, records_total,
  records_success, records_failed, errors(json), status, triggered_by(→users)
```

### Visitor + Inventory + Live Class (Modules 33, 34, 35)

```
visitor_logs
  id, school_id, visitor_name, id_number, phone, photo_path, purpose,
  host_user_id(→users), badge_no, checked_in_at, checked_out_at,
  logged_by(→users), items_carried(json), is_blacklisted, note

visitor_blacklist
  id, school_id, id_number, full_name, reason, added_by(→users)

asset_categories
  id, school_id, name, icon

assets
  id, school_id, asset_category_id, asset_code(unique), name, description,
  serial_number, purchased_at, purchase_price, warranty_until, location, photo_path,
  condition(excellent|good|fair|damaged|disposed),
  status(available|borrowed|maintenance|disposed), specs(json)

asset_loans
  id, school_id, asset_id, borrower_id(→users), approved_by(→users),
  borrowed_at, due_at, returned_at,
  status(pending|active|overdue|returned|lost), note

maintenance_requests
  id, school_id, asset_id, location_text, reported_by(→users),
  issue_description, photos(json), priority(low|medium|high|critical),
  status(reported|assigned|in_progress|resolved|rejected),
  assigned_to(→users), resolution_note, resolved_at, cost

video_providers
  id, school_id, name, slug,
  api_format(oauth_meeting_api|rest_room_api|self_hosted_jitsi|self_hosted_bbb|manual_link),
  base_url, client_id_encrypted, client_secret_encrypted, access_token_encrypted,
  extra_config(json), is_active

live_class_sessions
  id, school_id, class_section_id, subject_id, teacher_id(→users), video_provider_id,
  topic, scheduled_start, duration_minutes, meeting_id, join_url, host_url, passcode,
  status(scheduled|live|ended|cancelled), recording_url

live_class_attendances
  id, school_id, live_class_session_id, student_id, joined_at, left_at, total_minutes
  UNIQUE(live_class_session_id, student_id)
```

### Question Bank + Achievement + Scholarship (Modules 36, 37, 38)

```
question_bank_categories
  id, school_id, subject_id, name, parent_id(→question_bank_categories)

question_bank_items
  id, school_id, subject_id, question_bank_category_id, author_id(→users),
  question_html, type(mcq|multi_select|true_false|essay|fill_blank|matching|numeric),
  options(json), answer_key(json), explanation_html,
  difficulty(easy|medium|hard), cognitive_level(c1..c6), tags(json),
  used_count, avg_score_pct, discrimination, is_published

achievement_categories
  id, school_id, name, scope(internal|district|province|national|international), points

student_achievements
  id, school_id, student_id, achievement_category_id, title, achieved_at, issuer,
  certificate_path, description, verified, verified_by(→users)

certificate_templates
  id, school_id, name, layout_path, placeholders(json), is_default

digital_badges
  id, school_id, name, icon_path, description, award_criteria(json)

student_badges
  id, school_id, student_id, digital_badge_id, awarded_at
  UNIQUE(student_id, digital_badge_id)

scholarship_programs
  id, school_id, name, source(internal_school|external_donor|government|foundation),
  discount_type(percentage|fixed|full), discount_value, eligibility_criteria(json),
  open_date, close_date, quota, required_documents(json), is_active

scholarship_applications
  id, school_id, scholarship_program_id, student_id, documents(json), motivation,
  status(draft|submitted|review|interview|granted|rejected|withdrawn),
  reviewer_id(→users), reviewer_note, granted_from, granted_until

scholarship_grants
  id, school_id, scholarship_application_id, student_id, fee_invoice_id,
  discount_applied, applied_at
```

### Career Guidance + Curriculum + Yayasan (Modules 39, 40, 41)

```
career_assessments
  id, school_id, student_id, test_type(holland_riasec|mbti|cliftonstrengths|custom),
  responses(json), result(json), taken_at

college_database
  id, name, country, type(ptn|pts|international|vocational), city,
  majors_offered(json), passing_grade_avg, website

internship_placements
  id, school_id, student_id, company_name, position, mentor_name, mentor_phone,
  start_date, end_date, status(planned|active|completed|dropped),
  daily_logs(json), evaluation(json), certificate_path

industry_certifications
  id, school_id, student_id, cert_name, issuer, issued_at, expires_at,
  cert_number, file_path

curriculum_frameworks
  id, school_id, name, type(merdeka|k13|cambridge|ib|custom), config(json), is_active

curriculum_competencies
  id, school_id, curriculum_framework_id, subject_id, class_room_id,
  code, description, level_type(cp|tp|ki|kd|outcome),
  parent_id(→curriculum_competencies), indicators(json)

competency_lesson_map
  id, school_id, curriculum_competency_id, lesson_plan_id
  UNIQUE(curriculum_competency_id, lesson_plan_id)

competency_assessments
  id, school_id, student_id, curriculum_competency_id,
  mastery_level(emerging|developing|meets|exceeds),
  assessed_by(→users), assessed_at, evidence

foundations
  id, name, slug(unique), logo_path, address, npwp, contact(json), is_active

foundation_school_links
  id, foundation_id, school_id, joined_at, is_primary_school
  UNIQUE(foundation_id, school_id)

foundation_admins
  id, foundation_id, user_id, role(ketua_yayasan|pengurus|bendahara|sekretaris)
```

### Event + Daily Report + Extracurricular + Analytics (Modules 42-45)

```
school_events
  id, school_id, title, slug, description,
  event_type(parent_meeting|field_trip|festival|competition|workshop|seminar),
  starts_at, ends_at, venue, city, venue_lat, venue_lng,
  capacity, ticket_price(int), target_audience(json), cover_image_path,
  require_rsvp, is_published

event_rsvps
  id, school_id, school_event_id, user_id, guests_count,
  status(going|maybe|not_going|cancelled),
  payment_transaction_id, ticket_qr_token, checked_in_at
  UNIQUE(school_event_id, user_id)

daily_reports
  id, school_id, student_id, report_date,
  attendance(json), subjects_today(json), homework_due(json),
  canteen_summary(json), clinic_visit(json), discipline_events(json),
  wellness_checkin(json), teacher_notes(json), sent_at
  UNIQUE(student_id, report_date)

daily_report_preferences
  id, school_id, user_id(unique), enabled, preferred_send_time,
  channels(json), sections_enabled(json)

extracurriculars
  id, school_id, name, icon, description, coach_id(→users),
  schedule(json), capacity, fee_per_month(int), is_active

student_extracurriculars
  id, school_id, extracurricular_id, student_id, joined_at, left_at,
  level, achievements(json), is_active

extracurricular_attendances
  id, school_id, extracurricular_id, student_id, session_date,
  status(present|absent|late|excused), marked_by(→users)
  UNIQUE(extracurricular_id, student_id, session_date)

student_risk_scores
  id, school_id, student_id, snapshot_date,
  attendance_score, academic_score, behavior_score, engagement_score,
  overall_risk(0-100), risk_level(low|medium|high|critical),
  top_risk_factors(json), recommendations(json)
  UNIQUE(student_id, snapshot_date)

learning_analytics_reports
  id, school_id, scope(school|class|subject|student),
  class_section_id, subject_id, student_id,
  period_start, period_end, metrics(json), narrative,
  generated_by(→users)
```
