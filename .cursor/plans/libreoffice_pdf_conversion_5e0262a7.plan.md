---
name: LibreOffice PDF Conversion
overview: Replace the Microsoft Word COM automation PDF conversion with LibreOffice headless CLI, and address the Trajan Pro 3 font visibility issue for service-account execution.
todos:
  - id: rewrite-ps1
    content: "Rewrite scripts/convert-to-pdf.ps1: Replace Word COM with LibreOffice --headless CLI, keep mutex, handle output path renaming"
    status: completed
  - id: update-config
    content: "Update config/admin_config.php: Rename constants, add SOFFICE_PATH, update comments"
    status: completed
  - id: update-docxgen
    content: "Update services/DocxGenerator.php convertToPDF(): Update comments, error messages, and config references"
    status: completed
  - id: rewrite-setup
    content: "Rewrite scripts/setup-word-com.ps1: Check LibreOffice install, install Trajan Pro 3 system-wide, verify font visibility"
    status: completed
isProject: false
---

# LibreOffice PDF Conversion Migration

## Current Architecture

The DOCX-to-PDF pipeline uses **Microsoft Word COM Automation** via PowerShell:

```mermaid
flowchart LR
    PHP["DocxGenerator::convertToPDF()"] -->|proc_open| PS["convert-to-pdf.ps1"]
    PS -->|COM| Word["Word.Application"]
    Word -->|SaveAs2| PDF[PDF File]
```



**Key files:**

- `[scripts/convert-to-pdf.ps1](scripts/convert-to-pdf.ps1)` -- Word COM automation (139 lines)
- `[services/DocxGenerator.php](services/DocxGenerator.php)` -- PHP orchestrator, lines 331-456
- `[config/admin_config.php](config/admin_config.php)` -- Config constants, lines 196-204
- `[scripts/setup-word-com.ps1](scripts/setup-word-com.ps1)` -- One-time setup script (50 lines)

## Font Issue

**Trajan Pro 3 Regular** is installed only as a user-local font:

- Location: `C:\Users\Lenovo\AppData\Local\Microsoft\Windows\Fonts\Trajan Pro 3 Regular.otf`
- NOT in system-wide `C:\Windows\Fonts\`
- When Apache runs LibreOffice as SYSTEM/service account, user-local fonts are **invisible**
- The font must be installed system-wide for LibreOffice to use it during conversion

## Plan

### 1. Rewrite `scripts/convert-to-pdf.ps1` to use LibreOffice CLI

Replace the entire Word COM automation with a LibreOffice headless invocation:

```powershell
# Core conversion command
$soffice = "C:\Program Files\LibreOffice\program\soffice.exe"
& $soffice --headless --norestore --nolockcheck --convert-to pdf --outdir $outputDir $inputPath
```

Changes:

- Remove all Word COM object creation/cleanup code
- Remove Desktop folder creation workaround (not needed for LibreOffice)
- **Keep the mutex** -- LibreOffice can collide with itself if multiple instances share the same user profile
- Add `--nolockcheck` and `--norestore` flags for robustness
- Add a configurable `SOFFICE_PATH` with auto-detection fallback
- Keep the same parameter interface (`-inputPath`, `-outputPath`) so PHP code changes are minimal
- Handle the output path difference: LibreOffice outputs to a directory with auto-naming, so the script will rename the output file to match the expected `$outputPath`

### 2. Update `config/admin_config.php` (lines 196-204)

- Rename `WORD_CONVERT_SCRIPT` to `PDF_CONVERT_SCRIPT` (keep backward compat alias)
- Rename `WORD_CONVERT_TIMEOUT` to `PDF_CONVERT_TIMEOUT` (keep backward compat alias)
- Add `SOFFICE_PATH` constant pointing to `C:\Program Files\LibreOffice\program\soffice.exe`
- Update comments from "Microsoft Word" to "LibreOffice"

### 3. Update `services/DocxGenerator.php` (lines 331-456)

- Update `convertToPDF()` method comments (line 325: "Requires: Windows + Microsoft Word..." becomes "Requires: LibreOffice...")
- Update config constant references to use new names (with fallback to old names)
- Update error messages from "Word conversion" to "PDF conversion"
- The `proc_open` mechanism and timeout logic remain the same

### 4. Replace `scripts/setup-word-com.ps1` with LibreOffice setup/check script

Replace with a script that:

- Verifies LibreOffice is installed and `soffice.exe` is accessible
- Checks that Trajan Pro 3 font is installed **system-wide** in `C:\Windows\Fonts\`
- If not system-wide, copies it from the user-local location and registers it (requires admin)
- Verifies the font is visible to LibreOffice by running a quick headless test

### 5. Font installation note

The Trajan Pro 3 font needs to be copied to `C:\Windows\Fonts\` and registered in the registry at `HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Fonts`. The setup script will handle this, but it requires running as Administrator once.