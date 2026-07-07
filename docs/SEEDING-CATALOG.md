# Seeding Catalog

This file is the review surface for seed data before the PHP seeder structure is rewritten.

Goal:

- Keep global reference data separate from local fresh-install data.
- Preserve Ukrainian naming where the source data uses it.
- Make every assumption visible so it can be corrected before code generation.
- Treat private source files as analysis inputs only, never runtime inputs.

Source material:

- `docs/Customers DB.ods`
- `docs/BDB.txt`

Both files are ignored by Git and are used only to derive reviewed seed arrays.

## 1. Global Reference Seed

These records should exist for every environment that runs the seeders.

### 1.1 Staff roles

| Name | Can teach | Active | Note |
| --- | --- | --- | --- |
| `Викладач` | yes | yes | Teaching role used for staff linked to lessons and students. |
   



### 1.2 Lesson types

| Name | Duration, min | Lesson price | Teacher share | Assignable | Note |
| --- | --- | --- | --- | --- | --- |
| `Індивідуальний урок 300 ₴` | 60 | 300 | 150 | yes | Source row from `Травень26`. |
| `Індивідуальний урок 350 ₴` | 60 | 350 | 175 | yes | Source row from `Травень26`. |

### 1.3 Plans

These are complete monthly charges. The `4.5` values from the source are stored in the decimal `lesson_count` field and remain metadata only, not a billing input.

| Name | Duration, min | Lesson count | Monthly school price | Monthly teacher amount | Assignable | Note |
| --- | --- | --- | --- | --- | --- | --- |
| `Базовий` | 60 | 9 | 3950 | 1750 | yes | Source monthly package. |
| `Прогресивний` | 60 | 13 | 5600 | 2300 | yes | Source monthly package. |
| `Базовий-350` | 60 | 9 | 3060 | 1530 | yes | Source monthly package. |
| `Прогресивний-350` | 60 | 13 | 4600 | 2300 | yes | Source monthly package. |
| `Базовий-300` | 60 | 9 | 2625 | 1530 | yes | Source monthly package. |
| `Прогресивний-300` | 60 | 13 | 3950 | 2300 | yes | Source monthly package. |
| `Стартовий-45` | 45 | 4.5 | 975 | 570 | yes | Source monthly package. |
| `Стартовий-30` | 30 | 4.5 | 650 | 380 | yes | Source monthly package. |

### 1.4 Expense categories

| Name | Note |
| --- | --- |
| `Зарплата` | Automatic salary drafts. |
| `Реклама` | Advertising, including Facebook spend. |
| `Податки` | Tax payments. |
| `CRM` | Customer-management services, including Keepin CRM. |
| `Відеомонтаж` | Video creation and editing services, including Canvas. |

## 2. Local Fresh-Install Seed

These records are for `local` and `testing` only.

### 2.1 Accounts

| Type | Username | Email | Role | Linked staff | Locale | Password |
| --- | --- | --- | --- | --- | --- | --- |
| Admin | `admin` | `admin@example.com` | admin | yes | `en` | `password` |
| Teacher | `teacher` | `teacher@example.com` | teacher | yes | `en` | `password` |

### 2.2 Staff profiles

| Purpose | Name | Role | Compensation mode | Note |
| --- | --- | --- | --- | --- |
| Admin staff profile | `Адміністратор MICS HUB` | `Викладач` | dynamic | Administrative account linked to a teaching-capable staff record. |
| Teacher staff profile | `Максим Гузьо Степанович` | `Викладач` | dynamic | Source row from `Customers DB`. |




### 2.3 Pupils

Current rule:

- Seed only the pupils that belong to the reviewed teacher group.
- Preserve Ukrainian names.
- Do not preserve source class markers like `D1` and `D2` in names.
- Keep only a simple source note, not the source marker itself.

| Pupil name | Billing type | Lesson type or plan | Discount | Source note |
| --- | --- | --- | --- | --- |
| `Олексій Кравченко` | per-lesson | `Індивідуальний урок 300 ₴` | 0 | Source row from `Травень26`. |
| `Горбатько Олексій` | per-lesson | `Індивідуальний урок 300 ₴` | 0 | Source row from `Травень26`. |
| `Кузін Микита` | per-lesson | `Індивідуальний урок 300 ₴` | 720 | Source row from `Травень26`. |
| `Максим Гринько` | per-lesson | `Індивідуальний урок 300 ₴` | 0 | Source row from `Травень26`. |
| `Олег Нікітін` | per-lesson | `Індивідуальний урок 300 ₴` | 0 | Source row from `Травень26`. |
| `Кірієнко Юрій` | per-lesson | `Індивідуальний урок 300 ₴` | 0 | Source row from `Травень26`. |
| `Паша Семініхін` | plan-based | `Базовий` | 1000 | Source row from `Травень26`. |
| `Сагдіс Ліда` | per-lesson | `Індивідуальний урок 350 ₴` | 0 | Source row from `Травень26`. |
| `Сагдіс Тімур` | per-lesson | `Індивідуальний урок 350 ₴` | 0 | Source row from `Травень26`. |
| `Стрельников Данііл` | per-lesson | `Індивідуальний урок 300 ₴` | 1200 | Source row from `Травень26`. |
| `Литвиненко Кирило` | per-lesson | `Індивідуальний урок 300 ₴` | 1200 | Source row from `Травень26`. |
| `Кириленко Сергій` | per-lesson | `Індивідуальний урок 300 ₴` | 1200 | Source row from `Травень26`. |

### 2.4 Shared seed defaults

| Setting | Value | Reason |
| --- | --- | --- |
| Joined date | `2026-04-01` or another agreed April date | Use April instead of the current month. |
| Plan start date | same as joined date unless corrected | Keeps the first charge month deterministic. |
| Locale | `en` | English remains the default. |
| Timezone | `Europe/Kyiv` | One shared timezone for everyone. |

## 3. Decisions Already Made

These are the current assumptions I should use unless you override them.

1. `D1` and `D2` should not be preserved in names.
2. `4.5` source values are now stored in the decimal `lesson_count` field.
3. Source rows from the private databases may inform the seed, but the runtime seed must be PHP arrays only.
4. Rows with no rate or plan are ignored for now.
5. English remains the default locale for both seeded accounts.
6. The fresh seed should stay small: one admin, one teacher, and a limited set of pupils.
7. Ukrainian naming should be preserved where the source contains a real name or category name.

## 4. Confirmed Corrections

These were the review points that were applied in the rewritten seed structure:

1. `joined_at` and `plan_start_at` use `2026-04-01`.
2. The teacher staff profile keeps the full patronymic `Степанович`.
3. The pupil list stays at the 12 reviewed rows.
4. Source tags are not preserved in names.
5. `4.5` is stored in the decimal plan lesson-count field.

## 5. Intended Seeder Split

When this catalog is confirmed, I will rewrite the actual seeders into this structure:

- `DatabaseSeeder`
- `ReferenceDataSeeder`
- `SchoolDataSeeder`

That split keeps global reference rows separate from local fresh-install data and makes the review surface smaller for future changes.
