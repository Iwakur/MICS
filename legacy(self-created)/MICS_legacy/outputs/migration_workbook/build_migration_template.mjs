import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { Workbook, SpreadsheetFile } from "@oai/artifact-tool";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const workbook = Workbook.create();

function setCell(sheet, cell, value) {
  sheet.getRange(cell).values = [[value]];
}

function setRow(sheet, startCell, values) {
  const match = /^([A-Z]+)(\d+)$/.exec(startCell);
  if (!match) throw new Error(`Bad cell ref: ${startCell}`);
  const [, col, row] = match;
  const endCol = columnToLetters(lettersToColumn(col) + values.length - 1);
  sheet.getRange(`${col}${row}:${endCol}${row}`).values = [values];
}

function setBlock(sheet, startCell, rows) {
  const match = /^([A-Z]+)(\d+)$/.exec(startCell);
  if (!match) throw new Error(`Bad cell ref: ${startCell}`);
  const [, col, rowStr] = match;
  const row = Number(rowStr);
  const maxCols = Math.max(...rows.map((r) => r.length));
  const endCol = columnToLetters(lettersToColumn(col) + maxCols - 1);
  const endRow = row + rows.length - 1;
  const normalized = rows.map((r) => {
    const clone = [...r];
    while (clone.length < maxCols) clone.push("");
    return clone;
  });
  sheet.getRange(`${col}${row}:${endCol}${endRow}`).values = normalized;
}

function lettersToColumn(letters) {
  let n = 0;
  for (const ch of letters) n = n * 26 + (ch.charCodeAt(0) - 64);
  return n;
}

function columnToLetters(n) {
  let s = "";
  while (n > 0) {
    const mod = (n - 1) % 26;
    s = String.fromCharCode(65 + mod) + s;
    n = Math.floor((n - mod) / 26);
  }
  return s;
}

function styleTitle(range) {
  range.format.fill.color = "#16324f";
  range.format.font.color = "#ffffff";
  range.format.font.bold = true;
  range.format.font.size = 14;
}

function styleHeader(range) {
  range.format.fill.color = "#dbeafe";
  range.format.font.bold = true;
  range.format.verticalAlignment = "center";
  range.format.wrapText = true;
}

function styleNote(range) {
  range.format.fill.color = "#f8fafc";
  range.format.font.color = "#334155";
  range.format.wrapText = true;
}

function addSheet(name, zoom = 110) {
  const sheet = workbook.worksheets.add(name);
  try {
    sheet.view.zoomScale = zoom;
  } catch {}
  return sheet;
}

function finalizeSheet(sheet, usedRange, freezeCell = "A2", widths = []) {
  if (freezeCell) {
    const match = /^([A-Z]+)(\d+)$/.exec(freezeCell);
    if (match) {
      try {
        sheet.freezePanes(Number(match[2]) - 1, lettersToColumn(match[1]) - 1);
      } catch {}
    }
  }
  if (widths.length) {
    widths.forEach(([col, px]) => {
      try {
        sheet.getRange(`${col}:${col}`).format.columnWidthPx = px;
      } catch {}
    });
  }
  try {
    sheet.getRange(usedRange).format.wrapText = true;
  } catch {}
}

const intro = addSheet("README", 120);
setCell(intro, "A1", "MICS Migration Workbook");
styleTitle(intro.getRange("A1:F1"));
setBlock(intro, "A3", [
  ["Purpose", "Use this workbook with your employer to remove uncertainty before the final SQL migration from Excel to the current MICS database logic."],
  ["How to use", "Fill the yellow input cells and add comments where decisions are unclear. Do not delete the problem rows; mark them as resolved or choose a rule."],
  ["Main outcome", "Once this workbook is complete, it becomes the source of truth for writing a deterministic migration SQL script."],
  ["Current DB targets", "staff, users, plans, students, student_charges, payments, expenses, staff_payouts"],
  ["Known constraints", "The current schema has an integer lesson_count, so values like 4.5 need an agreed business rule before migration."],
]);
styleHeader(intro.getRange("A3:B3"));
styleNote(intro.getRange("A3:B7"));
finalizeSheet(intro, "A1:F12", "A3", [["A", 170], ["B", 760], ["C", 120], ["D", 120], ["E", 120], ["F", 120]]);

const decisions = addSheet("Decisions");
setCell(decisions, "A1", "Migration Decisions");
styleTitle(decisions.getRange("A1:J1"));
setRow(decisions, "A3", ["Section", "Topic", "Decision Needed", "Employer Answer", "Status", "Priority", "Owner", "Date", "Notes", "Final Rule"]);
styleHeader(decisions.getRange("A3:J3"));
setBlock(decisions, "A4", [
  ["Scope", "Migration scope", "Which entities migrate now?", "", "Open", "High", "", "", "Examples: students only / students + staff + plans / full current snapshot", ""],
  ["Time", "Sheet meaning", "Is Excel a snapshot or historical ledger?", "", "Open", "High", "", "", "You said history can be skipped for now", ""],
  ["Students", "One row = one student", "Are rows with '/' a single CRM record or multiple people?", "", "Open", "High", "", "", "Critical for person identity", ""],
  ["Teachers", "Alias mapping", "Confirm that aliases like Максим, Артем, Дмитро map 1-to-1 to staff", "", "Open", "High", "", "", "We inferred this from the sheet", ""],
  ["Plans", "Canonical plans", "Are Стартовий / Базовий / Прогресивний fixed business plans?", "", "Open", "High", "", "", "Needed to avoid too many plan variants", ""],
  ["Plans", "Custom numeric prices", "When price is numeric instead of plan name, is it custom tariff or shorthand for a known plan?", "", "Open", "High", "", "", "", ""],
  ["Students", "Joined date rule", "What should joined_at be when only the first-month flag exists?", "", "Open", "Medium", "", "", "Possible rule: first day of represented month", ""],
  ["Finance", "Payments history", "Do we skip old payments completely?", "", "Open", "High", "", "", "", ""],
  ["Finance", "Current month charges", "Should we create student_charges for the current month?", "", "Open", "Medium", "", "", "", ""],
  ["Finance", "Expense import", "Should service expense rows become real expenses in DB?", "", "Open", "Medium", "", "", "", ""],
  ["Finance", "Salary import", "Should salary rows become staff_payouts or remain reference data only?", "", "Open", "Medium", "", "", "", ""],
]);
finalizeSheet(decisions, "A1:J20", "A4", [["A", 120], ["B", 150], ["C", 320], ["D", 260], ["E", 100], ["F", 90], ["G", 110], ["H", 100], ["I", 360], ["J", 280]]);

const students = addSheet("Student Mapping");
setCell(students, "A1", "Student Mapping Rules");
styleTitle(students.getRange("A1:L1"));
setRow(students, "A3", ["Excel Column / Pattern", "Business Meaning", "DB Table", "DB Field", "Required Transform", "Example from Excel", "Problem / Risk", "Employer Rule", "Confirmed", "Owner", "Priority", "Notes"]);
styleHeader(students.getRange("A3:L3"));
setBlock(students, "A4", [
  ["ПІ", "Student display name", "students", "first_name / middle_name / last_name", "Split full name into CRM fields", "D2 Олексій Кравченко", "Names with prefixes and compound rows are ambiguous", "", "", "", "High", ""],
  ["Prefix D1 / D2 / A1 / M1 / M2", "Possible cohort or segment tag", "students", "comments or future tag field", "Preserve raw prefix unless employer defines logic", "D1 Горбатько Олексій", "Meaning currently unknown", "", "", "", "High", ""],
  ["Відповідальність", "Responsible teacher", "students", "staff_id", "Map alias to a teacher staff record", "Максим", "Wrong mapping breaks ownership", "", "", "", "High", ""],
  ["Ціна за урок", "Plan label or custom price", "plans", "name / lesson_price", "Normalize into canonical plan or custom tariff", "Стартовий / 300,00 ₴", "Mixed semantics in one column", "", "", "", "High", ""],
  ["Занять в місяць", "Monthly lessons", "plans", "lesson_count", "Numeric normalization", "4,5", "Schema requires integer", "", "", "", "High", ""],
  ["Вартість кінцева", "Final monthly amount", "students or student_charges", "amount input", "Currency cleanup", "2.050,00 ₴", "Needed to validate plan math", "", "", "", "High", ""],
  ["Знижка", "Discount amount", "students", "discount_amount", "Currency cleanup to numeric", "500,00 ₴", "Need to confirm whether discount is monthly fixed amount", "", "", "", "Medium", ""],
  ["Підтвердження оплати", "Payment confirmation flag", "payments or comments", "status/business rule", "Map only if business meaning is confirmed", "TRUE / FALSE", "Boolean does not contain full payment data", "", "", "", "High", ""],
  ["Перший місяць учня", "First month indicator", "students", "joined_at", "Map using agreed date rule", "TRUE / FALSE", "Not an actual date", "", "", "", "High", ""],
  ["Кометарі", "Free note", "students", "comments", "Copy text as-is", "Пішли на один місяць", "Low risk", "", "", "", "Low", ""],
]);
finalizeSheet(students, "A1:L18", "A4", [["A", 220], ["B", 180], ["C", 110], ["D", 180], ["E", 220], ["F", 180], ["G", 210], ["H", 220], ["I", 90], ["J", 90], ["K", 90], ["L", 220]]);

const staff = addSheet("Staff Mapping");
setCell(staff, "A1", "Staff and Teacher Mapping Rules");
styleTitle(staff.getRange("A1:K1"));
setRow(staff, "A3", ["Excel Field", "Business Meaning", "DB Table", "DB Field", "Transform", "Example", "Risk", "Employer Rule", "Confirmed", "Priority", "Notes"]);
styleHeader(staff.getRange("A3:K3"));
setBlock(staff, "A4", [
  ["ПІБ", "Staff full name", "staff", "first_name / middle_name / last_name", "Split full name", "Савицький Артем Васильович", "Name splitting may be imperfect", "", "", "High", ""],
  ["Short alias block", "Teacher shorthand", "staff", "tag or comments", "Map alias to exact teacher", "Артем", "Wrong alias breaks student ownership", "", "", "High", ""],
  ["Професія/роль", "Role", "staff", "role", "Copy or normalize role name", "Асистент / Бухгалтер", "Need rule for teacher rows showing technical placeholder text", "", "", "Medium", ""],
  ["Рахунок оплати", "Payout destination reference", "staff", "payout_card_number or comments", "Usually preserve as comment/reference", "Див. BDB.txt", "Not a real card number in the sheet", "", "", "Medium", ""],
  ["Зарплата", "Monthly salary / payout", "staff_payouts", "amount", "Currency to numeric", "7.800,00 ₴", "Need to know whether this is actual payout or target amount", "", "", "Medium", ""],
  ["Student count helper", "Reference metric", "staff", "comments only", "Preserve if useful", "15 / 21", "Not core transactional data", "", "", "Low", ""],
]);
finalizeSheet(staff, "A1:K14", "A4", [["A", 180], ["B", 180], ["C", 110], ["D", 160], ["E", 180], ["F", 190], ["G", 210], ["H", 220], ["I", 90], ["J", 90], ["K", 220]]);

const plans = addSheet("Plan Catalog");
setCell(plans, "A1", "Canonical Plan and Tariff Mapping");
styleTitle(plans.getRange("A1:M1"));
setRow(plans, "A3", ["Excel Pattern", "Canonical Plan?", "Canonical Plan Name", "Lessons / Month", "Allows Fractional Lessons?", "Final Monthly Price", "Teacher Monthly Share", "Derived Lesson Price", "Derived Share / Lesson", "DB Action", "Active?", "Confirmed", "Notes"]);
styleHeader(plans.getRange("A3:M3"));
setBlock(plans, "A4", [
  ["Стартовий", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Базовий", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Прогресивний", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Стартовий-350", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Базовий-350", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Прогресивний-350", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Стартовий-300", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Базовий-300", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Прогресивний-300", "", "", "", "", "", "", "", "", "Create or map", "", "", ""],
  ["Numeric-only price rows", "", "", "", "", "", "", "", "", "Decide custom tariff rule", "", "", "Examples: 300,00 ₴ / 600,00 ₴ / 1.250,00 ₴"],
]);
finalizeSheet(plans, "A1:M18", "A4", [["A", 190], ["B", 110], ["C", 180], ["D", 110], ["E", 140], ["F", 130], ["G", 140], ["H", 130], ["I", 130], ["J", 130], ["K", 90], ["L", 90], ["M", 220]]);

const finance = addSheet("Finance Rules");
setCell(finance, "A1", "Finance and Accounting Migration Rules");
styleTitle(finance.getRange("A1:K1"));
setRow(finance, "A3", ["Excel Data", "Business Meaning", "DB Table", "DB Field(s)", "Migrate Now?", "Transform Rule", "Example", "Risk", "Employer Answer", "Confirmed", "Notes"]);
styleHeader(finance.getRange("A3:K3"));
setBlock(finance, "A4", [
  ["Student monthly amount", "Current month fee", "student_charges", "amount / charge_month", "", "Create only if snapshot should become live accounting", "2.050,00 ₴", "May create false history if misused", "", "", ""],
  ["Payment confirmed flag", "Actual payment happened?", "payments", "amount / payment_date / status", "", "Only if employer confirms it is real payment evidence", "TRUE", "Boolean alone is insufficient for accounting-quality payment docs", "", "", ""],
  ["Service expense rows", "Current month operating expenses", "expenses", "expense_date / category / amount", "", "Map service names to categories", "Facebook / Податкова / Canvas", "Need category policy", "", "", ""],
  ["Salary rows", "Current month staff payout", "staff_payouts", "payout_date / amount", "", "Use only if salary means actual payout obligation", "7.800,00 ₴", "Could be planning data rather than final payout", "", "", ""],
  ["Summary KPI rows", "Analytics only", "none", "n/a", "", "Do not migrate as transactional records", "Рентабельність / Оберти", "Would pollute DB if treated as entities", "", "", ""],
]);
finalizeSheet(finance, "A1:K14", "A4", [["A", 190], ["B", 170], ["C", 130], ["D", 180], ["E", 90], ["F", 220], ["G", 180], ["H", 220], ["I", 180], ["J", 90], ["K", 220]]);

const problems = addSheet("Known Problems");
setCell(problems, "A1", "Known Migration Problems");
styleTitle(problems.getRange("A1:H1"));
setRow(problems, "A3", ["Problem", "Why It Matters", "Current Impact", "Decision Needed", "Proposed Options", "Employer Decision", "Resolved?", "Notes"]);
styleHeader(problems.getRange("A3:H3"));
setBlock(problems, "A4", [
  ["4,5 lessons is not an integer", "DB plans.lesson_count currently expects integer", "Plan math and payouts can distort", "Choose handling rule", "Round up to 5 / redesign meaning / split into special half-month plan", "", "", ""],
  ["Rows with '/' in student names", "May represent multiple people in one CRM row", "Identity and reporting ambiguity", "Choose CRM rule", "Keep as one client / split into separate students / convert to household label", "", "", ""],
  ["Rows with - ₴ or missing totals", "No reliable commercial amount", "Cannot create trustworthy charges or tariff mapping", "Choose unresolved policy", "Skip / import inactive / import with explicit unresolved comment", "", "", ""],
  ["Mixed plan labels and numeric prices", "Creates too many fake plan variants if mapped blindly", "Plan catalog becomes messy", "Choose canonical tariff policy", "Strict catalog / catalog + custom plans / numeric rows map to named plans", "", "", ""],
  ["Boolean first-month flag instead of date", "students.joined_at needs actual datetime", "Current import would rely on inference", "Choose date fallback", "First day of represented month / manual completion / leave unresolved", "", "", ""],
  ["Boolean payment confirmation instead of payment details", "payments table needs more than yes/no", "Cannot reconstruct good payment history", "Choose finance migration rule", "Skip history / create only current state / ask for bank statement source", "", "", ""],
  ["Unknown meaning of D1/D2/A1/M1/M2 prefixes", "Could contain business segmentation", "Loss of meaning if discarded", "Choose preservation rule", "Comments only / separate tag dictionary / ignore", "", "", ""],
]);
finalizeSheet(problems, "A1:H14", "A4", [["A", 220], ["B", 230], ["C", 180], ["D", 180], ["E", 260], ["F", 180], ["G", 90], ["H", 220]]);

const questions = addSheet("Questions to Ask");
setCell(questions, "A1", "Questions for Employer Interview");
styleTitle(questions.getRange("A1:F1"));
setRow(questions, "A3", ["#", "Question", "Why We Need It", "Expected Answer Type", "Answer", "Follow-up Notes"]);
styleHeader(questions.getRange("A3:F3"));
setBlock(questions, "A4", [
  [1, "Is one Excel student row always one CRM student record?", "Defines identity model for migration", "Yes/No + explanation", "", ""],
  [2, "What do D1, D2, A1, M1, M2 prefixes mean?", "Preserves business semantics", "Dictionary / explanation", "", ""],
  [3, "Are names with '/' one student, siblings, pair lessons, or separate clients?", "Defines whether rows must be split", "Rule", "", ""],
  [4, "Are Стартовий / Базовий / Прогресивний fixed tariffs?", "Needed for canonical plan mapping", "Yes/No + plan definitions", "", ""],
  [5, "When price is numeric instead of plan name, is that a custom tariff or shorthand for a known plan?", "Avoids fake plans", "Rule", "", ""],
  [6, "Is Знижка always a monthly fixed money discount?", "Needed for student discount_amount logic", "Yes/No + exceptions", "", ""],
  [7, "Is Вартість кінцева the exact amount the student should pay this month?", "Validates charge basis", "Yes/No", "", ""],
  [8, "Is Доля вчителя monthly total teacher share or per-lesson share?", "Needed to derive plan payout logic", "Rule", "", ""],
  [9, "What do we do with 4,5 lessons?", "Schema mismatch", "Chosen handling rule", "", ""],
  [10, "Does Підтвердження оплати = TRUE mean money was actually received?", "Defines whether payments can be created", "Yes/No + nuance", "", ""],
  [11, "If history is skipped, should we still create current-month charges?", "Defines operational starting point", "Yes/No", "", ""],
  [12, "Should salaries become actual staff_payout rows?", "Defines finance migration scope", "Yes/No", "", ""],
  [13, "Should service expense rows become real expenses in DB?", "Defines expense migration scope", "Yes/No", "", ""],
  [14, "Should rows with - ₴ be skipped or imported as unresolved/inactive students?", "Defines unresolved policy", "Rule", "", ""],
  [15, "Is missing phone/email acceptable for phase one?", "Defines CRM completeness standard", "Yes/No", "", ""],
  [16, "Should all imported snapshot students start as active?", "Defines student status initialization", "Yes/No + exceptions", "", ""],
  [17, "What exact month does this Excel sheet represent?", "Defines date anchors for snapshot migration", "Month", "", ""],
  [18, "Should first-month = TRUE set joined_at to the first day of that month?", "Defines joined_at fallback", "Yes/No", "", ""],
]);
finalizeSheet(questions, "A1:F24", "A4", [["A", 60], ["B", 380], ["C", 250], ["D", 150], ["E", 220], ["F", 260]]);

const glossary = addSheet("Glossary");
setCell(glossary, "A1", "Glossary and Reference");
styleTitle(glossary.getRange("A1:D1"));
setRow(glossary, "A3", ["Term", "Meaning in This Migration", "Current DB Place", "Notes"]);
styleHeader(glossary.getRange("A3:D3"));
setBlock(glossary, "A4", [
  ["Snapshot migration", "Load current state without reconstructing full history", "students / plans / staff / optionally current finance docs", "This is the likely phase-one approach"],
  ["Canonical plan", "Approved tariff definition reused across many students", "plans", "Prevents dozens of accidental custom plans"],
  ["Custom tariff", "One-off pricing that does not match canonical plan set", "plans", "Allowed only if employer confirms"],
  ["Teacher alias", "Short name used in Excel to identify responsible teacher", "staff mapping", "Examples: Максим, Артем"],
  ["Unresolved row", "Excel row that cannot be safely mapped yet", "manual review", "Should be explicitly tracked, not silently guessed"],
]);
finalizeSheet(glossary, "A1:D12", "A4", [["A", 150], ["B", 360], ["C", 180], ["D", 280]]);

for (const sheet of workbook.worksheets.items) {
  try {
    sheet.gridLinesVisible = true;
  } catch {}
}

const output = await SpreadsheetFile.exportXlsx(workbook);
const outputPath = path.join(__dirname, "MICS_Migration_Worksheet.xlsx");
await output.save(outputPath);

console.log(outputPath);
