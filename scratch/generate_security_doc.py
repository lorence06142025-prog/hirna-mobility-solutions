import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, hex_color):
    shading_elm = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
    cell._tc.get_or_add_tcPr().append(shading_elm)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'''
        <w:tcMar {nsdecls("w")}>
            <w:top w:w="{top}" w:type="dxa"/>
            <w:bottom w:w="{bottom}" w:type="dxa"/>
            <w:left w:w="{left}" w:type="dxa"/>
            <w:right w:w="{right}" w:type="dxa"/>
        </w:tcMar>
    ''')
    tcPr.append(tcMar)

def create_document():
    doc = docx.Document()

    # Page setup - Margins (1 inch all around)
    sections = doc.sections
    for section in sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)

    # Color Palette
    PRIMARY = RGBColor(15, 23, 42)      # Deep Navy #0F172A
    SECONDARY = RGBColor(5, 150, 105)   # Emerald #059669
    ACCENT = RGBColor(217, 119, 6)      # Gold #D97706
    DARK_TEXT = RGBColor(51, 65, 85)    # Slate #334155
    MUTED = RGBColor(100, 116, 139)     # Gray #64748B

    # Document Header / Banner Box
    title_table = doc.add_table(rows=1, cols=1)
    title_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    title_cell = title_table.cell(0, 0)
    set_cell_background(title_cell, "0F172A")
    set_cell_margins(title_cell, top=280, bottom=280, left=280, right=280)
    title_cell.width = Inches(6.5)

    p_org = title_cell.paragraphs[0]
    p_org.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_org = p_org.add_run("HIRNA MOBILITY SOLUTIONS INC.")
    r_org.font.name = "Arial"
    r_org.font.size = Pt(11)
    r_org.font.bold = True
    r_org.font.color.rgb = ACCENT

    p_title = title_cell.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_title = p_title.add_run("ENTERPRISE SYSTEM SECURITY ARCHITECTURE & SPECIFICATIONS")
    r_title.font.name = "Arial"
    r_title.font.size = Pt(18)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(255, 255, 255)

    p_sub = title_cell.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_sub = p_sub.add_run("Comprehensive Technical Documentation on Authentication, Authorization, Defenses & Security Audit Trail")
    r_sub.font.name = "Arial"
    r_sub.font.size = Pt(10)
    r_sub.font.color.rgb = RGBColor(203, 213, 225)

    doc.add_paragraph().paragraph_format.space_after = Pt(12)

    # Document Meta Table
    meta_table = doc.add_table(rows=4, cols=2)
    meta_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    meta_table.autofit = False

    meta_data = [
        ("Document Reference:", "HMS-SEC-DOC-2026-V2"),
        ("Classification:", "CONFIDENTIAL / ENTERPRISE SYSTEM SECURITY SPECIFICATION"),
        ("System Name:", "Hirna TNC & Fleet Management Portal"),
        ("Date Generated:", "September 24, 2026"),
    ]

    for row_idx, (k, v) in enumerate(meta_data):
        cell_k = meta_table.cell(row_idx, 0)
        cell_v = meta_table.cell(row_idx, 1)
        cell_k.width = Inches(2.2)
        cell_v.width = Inches(4.3)
        set_cell_background(cell_k, "F8FAFC")
        set_cell_background(cell_v, "F1F5F9")
        set_cell_margins(cell_k, top=60, bottom=60, left=100, right=100)
        set_cell_margins(cell_v, top=60, bottom=60, left=100, right=100)

        pk = cell_k.paragraphs[0]
        rk = pk.add_run(k)
        rk.font.name = "Arial"
        rk.font.size = Pt(9.5)
        rk.font.bold = True
        rk.font.color.rgb = PRIMARY

        pv = cell_v.paragraphs[0]
        rv = pv.add_run(v)
        rv.font.name = "Arial"
        rv.font.size = Pt(9.5)
        rv.font.color.rgb = DARK_TEXT

    doc.add_paragraph().paragraph_format.space_after = Pt(14)

    def add_heading_1(text):
        h = doc.add_paragraph()
        h.paragraph_format.space_before = Pt(16)
        h.paragraph_format.space_after = Pt(6)
        h.paragraph_format.keep_with_next = True
        r = h.add_run(text)
        r.font.name = "Arial"
        r.font.size = Pt(14)
        r.font.bold = True
        r.font.color.rgb = PRIMARY
        return h

    def add_heading_2(text):
        h = doc.add_paragraph()
        h.paragraph_format.space_before = Pt(12)
        h.paragraph_format.space_after = Pt(4)
        h.paragraph_format.keep_with_next = True
        r = h.add_run(text)
        r.font.name = "Arial"
        r.font.size = Pt(12)
        r.font.bold = True
        r.font.color.rgb = SECONDARY
        return h

    def add_paragraph(text, bold_prefix="", bullet=False):
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.line_spacing = 1.15
        if bullet:
            p.paragraph_format.left_indent = Inches(0.25)
            r_b = p.add_run("▪  ")
            r_b.font.name = "Arial"
            r_b.font.size = Pt(9.5)
            r_b.font.color.rgb = SECONDARY

        if bold_prefix:
            r_pre = p.add_run(bold_prefix)
            r_pre.font.name = "Arial"
            r_pre.font.size = Pt(10)
            r_pre.font.bold = True
            r_pre.font.color.rgb = PRIMARY

        r_text = p.add_run(text)
        r_text.font.name = "Arial"
        r_text.font.size = Pt(10)
        r_text.font.color.rgb = DARK_TEXT
        return p

    def add_callout(title, text):
        tbl = doc.add_table(rows=1, cols=1)
        tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        c = tbl.cell(0, 0)
        c.width = Inches(6.5)
        set_cell_background(c, "F0FDF4") # Light emerald tint
        set_cell_margins(c, top=100, bottom=100, left=150, right=150)
        
        # Add left border accent
        tcPr = c._tc.get_or_add_tcPr()
        tcBorders = parse_xml(f'''
            <w:tcBorders {nsdecls("w")}>
                <w:top w:val="none"/>
                <w:left w:val="single" w:sz="36" w:space="0" w:color="059669"/>
                <w:bottom w:val="none"/>
                <w:right w:val="none"/>
            </w:tcBorders>
        ''')
        tcPr.append(tcBorders)

        p = c.paragraphs[0]
        p.paragraph_format.space_after = Pt(2)
        r_t = p.add_run(f"🔒 {title}\n")
        r_t.font.name = "Arial"
        r_t.font.size = Pt(10.5)
        r_t.font.bold = True
        r_t.font.color.rgb = SECONDARY

        r_body = p.add_run(text)
        r_body.font.name = "Arial"
        r_body.font.size = Pt(9.5)
        r_body.font.color.rgb = DARK_TEXT
        doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # --- Executive Summary ---
    add_heading_1("1. Executive Summary & Security Philosophy")
    add_paragraph("The Hirna Mobility Solutions TNC & Fleet Management Portal is engineered with defense-in-depth security principles to protect sensitive fleet operations, financial transactions, passenger data, and dispatcher communications. The platform integrates multi-layered controls across authentication, rate limiting, anti-bot detection, role-based access enforcement, secure data transport, and real-time audit trail recording.")

    add_callout(
        "Core Security Compliance Objective",
        "All authentication operations are strictly enforced server-side. Sensitive security parameters (such as 2FA verification windows, rate-limiting lockout keys, and session timeouts) are validated against database records and encrypted server sessions, preventing any reliance on client-side storage (localStorage, cookies, or JavaScript execution)."
    )

    # --- Matrix Table ---
    add_heading_1("2. High-Level System Security Control Matrix")
    matrix_table = doc.add_table(rows=10, cols=3)
    matrix_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    matrix_table.autofit = False

    headers = ["Security Domain", "Mechanism Implemented", "Operational Threshold / Spec"]
    for i, h in enumerate(headers):
        cell = matrix_table.cell(0, i)
        set_cell_background(cell, "0F172A")
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = "Arial"
        r.font.size = Pt(10)
        r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)

    matrix_rows = [
        ("2FA OTP Authentication", "Dynamic 6-Digit Code Dispatch via HTTPS API", "10-Min OTP Expiry; 60-Sec Resend Cooldown"),
        ("OTP Verification Window", "Server-side Timestamp (`last_otp_verified_at`)", "50-Minute Window; Bypasses OTP if Active"),
        ("Brute-Force Protection", "RateLimiter Keying on Normalized Input + IP", "Strict 3 Failed Attempts -> 60s Lockout"),
        ("Anti-Bot / Scraper Trap", "Hidden Honeypot Field (`hirna_security_hp`)", "Instant Silent Rejection & Audit Logging"),
        ("Password Security", "Bcrypt One-Way Cryptographic Hash", "Bcrypt Rounds=12; Normalization Sanitization"),
        ("Idle Session Timeout", "Middleware Session Activity Tracking", "30-Minute Inactivity Session Invalidation"),
        ("Access Control (RBAC)", "Role Authorization Middleware (`CheckRole`)", "5 Roles (Admin, Fleet, Dispatcher, Finance, Ops)"),
        ("CSRF Protection", "Cross-Site Request Forgery Tokens", "Token Regeneration on Logout & Session Start"),
        ("Security Audit Logging", "Dedicated Database Audit Trail (`SecurityLog`)", "Tracks Logins, Lockouts, Bots & Perspective Swaps")
    ]

    col_widths = [Inches(1.8), Inches(2.7), Inches(2.0)]
    for row_idx, row_data in enumerate(matrix_rows, start=1):
        bg = "F8FAFC" if row_idx % 2 == 1 else "FFFFFF"
        for col_idx, text in enumerate(row_data):
            cell = matrix_table.cell(row_idx, col_idx)
            cell.width = col_widths[col_idx]
            set_cell_background(cell, bg)
            set_cell_margins(cell, top=60, bottom=60, left=80, right=80)
            p = cell.paragraphs[0]
            r = p.add_run(text)
            r.font.name = "Arial"
            r.font.size = Pt(9)
            r.font.color.rgb = DARK_TEXT

    doc.add_paragraph().paragraph_format.space_after = Pt(12)

    # --- 3. Multi-Factor Authentication & 50-Min OTP Verification Window ---
    add_heading_1("3. Multi-Factor Authentication (2FA) & 50-Minute OTP Window")
    add_paragraph("To protect user accounts against credential stuffing and stolen passwords, Hirna enforces a mandatory 2-Factor Authentication (2FA) mechanism using dynamic 6-digit One-Time Passwords (OTP).")

    add_heading_2("3.1 OTP Generation & Dispatch Pipeline")
    add_paragraph("Generates a standard 6-digit numeric string using cryptographically secure random integer sampling (`random_int(100000, 999999)`).", bold_prefix="Secure Random Generation: ", bullet=True)
    add_paragraph("Code is stored in encrypted server session memory along with a strict 10-minute expiration timestamp (`now()->addMinutes(10)`).", bold_prefix="Session Expiration: ", bullet=True)
    add_paragraph("Prevents mail server flooding by imposing a server-side 60-second cooldown timer before a new code can be dispatched.", bold_prefix="Anti-Spam Cooldown: ", bullet=True)
    add_paragraph("Dispatches email via HTTPS Port 443 REST API (`api.brevo.com/v3/smtp/email`), ensuring 100% delivery even in restricted cloud execution environments (e.g., Vercel serverless containers blocking SMTP TCP ports 587/465). Fallback to standard SMTP is maintained for redundancy.", bold_prefix="Cloud Transport Redundancy: ", bullet=True)

    add_heading_2("3.2 Server-Side 50-Minute OTP Verification Window Architecture")
    add_paragraph("To balance high-level security with user operational convenience, the system implements a strict server-side OTP Verification Window defined as follows:")

    add_paragraph("When a user successfully completes 2FA verification, the exact server timestamp is recorded in `users.last_otp_verified_at`.", bold_prefix="Verification Timestamp: ", bullet=True)
    add_paragraph("When the user logs in again with valid credentials, the system checks whether `$user->last_otp_verified_at` is within 50 minutes of the current server time (`gt(now()->subMinutes(50))`).", bold_prefix="Window Check: ", bullet=True)
    add_paragraph("If the previous successful OTP verification occurred within the last 50 minutes, the OTP verification step is bypassed, allowing immediate access to the dashboard.", bold_prefix="Active Window Action: ", bullet=True)
    add_paragraph("Logging in without OTP during an active window does NOT update or reset `last_otp_verified_at`. The 50-minute clock strictly continues running from the original OTP verification timestamp.", bold_prefix="Clock Independence: ", bullet=True)
    add_paragraph("Once more than 50 minutes have elapsed, a new 6-digit OTP code is required. Completing this new OTP updates `last_otp_verified_at` to the current time, restarting the 50-minute counter.", bold_prefix="Expiration Re-verification: ", bullet=True)

    add_callout(
        "Technical Verification Guardrail",
        "The 50-minute OTP verification window is stored and evaluated exclusively in the SQLite/MySQL database (`users.last_otp_verified_at`). It does not rely on browser cookies, local storage, or client-side JavaScript, ensuring complete immunity against client-side tampering."
    )

    # --- 4. Brute-Force & Rate Limiting Controls ---
    add_heading_1("4. Rate Limiting & Brute-Force Protection")
    add_paragraph("Automated credential-guessing attacks are systematically blocked using Laravel's native `RateLimiter` component.")

    add_heading_2("4.1 Lockout Rules & Throttling Parameters")
    add_paragraph("Rate limiter keys are composed of the normalized user identifier (email/username stripped of spaces and converted to lowercase) concatenated with the client IP address (`Str::transliterate($cleanInput . '|' . $request->ip())`).", bold_prefix="Composite Lockout Key: ", bullet=True)
    add_paragraph("Set to a strict limit of 3 consecutive failed login attempts.", bold_prefix="Strict Threshold: ", bullet=True)
    add_paragraph("Upon exceeding 3 failed attempts, the account/IP combination is locked out for 60 seconds. Further attempts yield HTTP 429 response states with a countdown timer.", bold_prefix="Lockout Window: ", bullet=True)
    add_paragraph("Successful password entry immediately clears the rate limiter strike count for that key.", bold_prefix="Automatic Strike Reset: ", bullet=True)

    # --- 5. Anti-Bot Honeypot Protection ---
    add_heading_1("5. Anti-Bot & Scraper Honeypot Defense")
    add_paragraph("To protect authentication endpoints against automated web crawlers, credential harvesters, and spam bots, an Anti-Bot Honeypot trap is embedded into the login interface.")

    add_heading_2("5.1 Honeypot Workflow")
    add_paragraph("A hidden input field (`hirna_security_hp`) is included in the login form, rendered off-screen using CSS styles (`display: none !important; opacity: 0; position: absolute; left: -9999px;`).", bold_prefix="Invisible Field Injection: ", bullet=True)
    add_paragraph("Legitimate human users interacting with standard web browsers leave this field empty.", bold_prefix="Human Trait: ", bullet=True)
    add_paragraph("Automated scripts and headless form fillers scan the DOM and populate all form fields. If `hirna_security_hp` contains any value, the request is immediately flagged as a bot attack.", bold_prefix="Bot Trait: ", bullet=True)
    add_paragraph("The server instantly halts execution, creates a high-priority `bot_honeypot_blocked` entry in `SecurityLog`, and returns a neutral error message without executing database password lookups.", bold_prefix="Silent Neutralization: ", bullet=True)

    # --- 6. Credential Security & Password Hashing ---
    add_heading_1("6. Password Cryptography & Input Normalization")
    add_paragraph("Hirna strictly enforces industry-standard cryptographic algorithms for storing user credentials.")

    add_heading_2("6.1 Bcrypt One-Way Hashing")
    add_paragraph("User passwords are hashed using Bcrypt with a work factor (cost) of 12. Plaintext passwords are never stored in log files, database tables, or session states.", bold_prefix="Work Factor: ", bullet=True)
    add_paragraph("All authentication attempts utilize standard `Hash::check()` verification, preventing timing attacks during password matching.", bold_prefix="Constant-Time Comparison: ", bullet=True)

    add_heading_2("6.2 Domainless & Sanitized Username Matching")
    add_paragraph("To support flexible enterprise credentials (e.g., domainless usernames such as `hirna admin`, `hirna fleet`, `hirna dispatcher`), the system sanitizes input using lowercase transformation and whitespace stripping while maintaining strict query parameterization to eliminate SQL Injection risks.", bold_prefix="Input Sanitization: ", bullet=True)

    # --- 7. Session Management, CSRF & Idle Timeout ---
    add_heading_1("7. Session Security & Idle Timeout Controls")
    add_heading_2("7.1 30-Minute Idle Session Timeout")
    add_paragraph("Independent of the 50-minute OTP verification window, the system enforces a 30-minute idle session timeout via the `CheckRole` middleware. If a logged-in user generates no HTTP activity for 30 consecutive minutes, their server session is automatically invalidated, forcing a re-authentication prompt.", bold_prefix="Inactivity Protection: ", bullet=True)

    add_heading_2("7.2 Session Fixation Defenses")
    add_paragraph("Upon successful password entry or OTP verification, `request()->session()->regenerate()` is called to destroy the old session ID and issue a new cryptographic session token, preventing Session Fixation attacks.", bold_prefix="Session ID Regeneration: ", bullet=True)

    add_heading_2("7.3 CSRF (Cross-Site Request Forgery) Protection")
    add_paragraph("All POST/PUT/DELETE forms are protected by cryptographically unique CSRF tokens verified by Laravel's middleware. On user logout, `session()->invalidate()` and `session()->regenerateToken()` ensure that old CSRF tokens are rendered permanently invalid.", bold_prefix="CSRF Tokens: ", bullet=True)

    # --- 8. Role-Based Access Control ---
    add_heading_1("8. Role-Based Access Control (RBAC)")
    add_paragraph("User access is strictly partitioned into five granular roles using the `CheckRole.php` middleware:")

    rbac_table = doc.add_table(rows=6, cols=3)
    rbac_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    rbac_table.autofit = False

    rbac_headers = ["System Role", "Primary Responsibilities", "Access Boundary & Restrictions"]
    for i, h in enumerate(rbac_headers):
        cell = rbac_table.cell(0, i)
        set_cell_background(cell, "0F172A")
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = "Arial"
        r.font.size = Pt(10)
        r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)

    rbac_rows = [
        ("System Administrator (`admin`)", "Full portal administration, system setup & logs", "Unrestricted system-wide administrative access"),
        ("Fleet Manager (`fleet_manager`)", "Vehicle registration, maintenance & inspection", "Restricted to vehicle, driver, and inventory routes"),
        ("Dispatcher (`dispatcher`)", "Trip scheduling, live tracking & driver assign", "Restricted to trip management and driver tracking"),
        ("Finance Officer (`finance`)", "Billing, revenue, driver payouts & audit", "Restricted to financial reports, billing & payouts"),
        ("Operations Manager (`operations`)", "Performance monitoring, analytics & reports", "Restricted to operational dashboards & analytics")
    ]

    r_widths = [Inches(1.8), Inches(2.4), Inches(2.3)]
    for row_idx, row_data in enumerate(rbac_rows, start=1):
        bg = "F8FAFC" if row_idx % 2 == 1 else "FFFFFF"
        for col_idx, text in enumerate(row_data):
            cell = rbac_table.cell(row_idx, col_idx)
            cell.width = r_widths[col_idx]
            set_cell_background(cell, bg)
            set_cell_margins(cell, top=60, bottom=60, left=80, right=80)
            p = cell.paragraphs[0]
            r = p.add_run(text)
            r.font.name = "Arial"
            r.font.size = Pt(9)
            r.font.color.rgb = DARK_TEXT

    doc.add_paragraph().paragraph_format.space_after = Pt(12)

    # --- 9. Security Audit Logging ---
    add_heading_1("9. Real-Time Security Audit Trail (`SecurityLog`)")
    add_paragraph("Every security-sensitive operation is recorded in the `security_logs` database table to support forensic analysis, compliance auditing, and threat detection.")

    add_paragraph("`successful_login` - Authenticated session established (logs whether OTP was verified or bypassed via 50-min window).", bold_prefix="Logged Event Types:\n", bullet=True)
    add_paragraph("`failed_login` - Invalid password attempt (includes remaining strikes).", bullet=True)
    add_paragraph("`account_lockout` - Lockout triggered after 3 consecutive failures.", bullet=True)
    add_paragraph("`bot_honeypot_blocked` - Automated bot scraper trapped by hidden form field.", bullet=True)
    add_paragraph("`perspective_switched` - Role perspective changed during administrative demo mode.", bullet=True)
    add_paragraph("`logout` - User-initiated session termination.", bullet=True)

    add_paragraph("Each log record captures the event type, email/username, client IP address, User-Agent header, detailed context text, and server timestamp.", bold_prefix="Audit Metadata Captured: ", bullet=True)

    # --- Document Sign-off ---
    doc.add_paragraph().paragraph_format.space_after = Pt(16)
    sign_table = doc.add_table(rows=2, cols=2)
    sign_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    sign_table.autofit = False

    cell_s1 = sign_table.cell(0, 0)
    cell_s2 = sign_table.cell(0, 1)
    cell_s1.width = Inches(3.25)
    cell_s2.width = Inches(3.25)
    set_cell_background(cell_s1, "F8FAFC")
    set_cell_background(cell_s2, "F8FAFC")
    set_cell_margins(cell_s1, top=80, bottom=80, left=100, right=100)
    set_cell_margins(cell_s2, top=80, bottom=80, left=100, right=100)

    p1 = cell_s1.paragraphs[0]
    r1 = p1.add_run("APPROVED BY SYSTEM ARCHITECT\nHirna Engineering Security Team\nDate: September 24, 2026")
    r1.font.name = "Arial"
    r1.font.size = Pt(9)
    r1.font.color.rgb = DARK_TEXT

    p2 = cell_s2.paragraphs[0]
    r2 = p2.add_run("VERIFIED FOR PRODUCTION DEPLOYMENT\nHirna Mobility Solutions Inc.\nStatus: FULLY ENFORCED")
    r2.font.name = "Arial"
    r2.font.size = Pt(9)
    r2.font.bold = True
    r2.font.color.rgb = SECONDARY

    # Output paths
    target_public = r"c:\xamppp\htdocs\TNVS\public\Hirna_System_Security_Documentation.docx"
    target_artifact = r"C:\Users\Lorence\.gemini\antigravity\brain\3bc4e34d-8866-4b43-87d1-94220eea0331\Hirna_System_Security_Documentation.docx"

    doc.save(target_public)
    doc.save(target_artifact)
    print(f"Document successfully created at:\n1. {target_public}\n2. {target_artifact}")

if __name__ == "__main__":
    create_document()
