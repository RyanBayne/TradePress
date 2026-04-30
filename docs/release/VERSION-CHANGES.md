# Version Changes Procedure

When prompted with "UPDATE VERSION" or similar version-related requests, execute this systematic routine:

## Scope

This procedure applies to release/version updates for the TradePress plugin. It is not a general changelog-writing guide; it is the checklist for keeping plugin metadata, constants, readme data, and release notes aligned.

### 1. Version Number Updates
Update version numbers in these files (use semantic versioning):
- `tradepress.php` - Main plugin header and TRADEPRESS_VERSION constant
- `readme.txt` - Stable tag and tested up to versions
- Any modified class files - Update @version tags

### 2. WordPress Core Version Detection
Check if WordPress has released a new version:
- Check the installed WordPress version from the local site admin or WP-CLI when available
- Check the current WordPress.org release/version documentation when preparing a public release
- Update "Tested up to" field in readme.txt if newer WordPress version detected
- Check plugin compatibility with latest WordPress version requirements

### 3. Changelog Management
Update `readme.txt` changelog section:
- Add new version entry with current date
- Categorize changes under:
  - **Faults Resolved** - Bug fixes and error corrections
  - **Feature Improvements** - New features and enhancements
  - **Technical Notes** - Developer-focused changes
  - **Configuration Advice** - User guidance for new features
  - **Database Changes** - Schema modifications (if any)

### 4. Version Type Guidelines
**MAJOR** (X.0.0) - Breaking changes, major architecture changes
**MINOR** (X.Y.0) - New features, backwards-compatible additions
**PATCH** (X.Y.Z) - Bug fixes, small improvements

### 5. Files to Update for Version Change
```
tradepress.php                              - Plugin header and constants
readme.txt                                  - Stable tag and changelog
loader.php                                  - Version references (if any)
Any modified class files                    - @version docblock tags
```

### 6. Changelog Entry Template
```
= X.Y.Z Released [Date] = 
* Faults Resolved
    - [Specific bug fixes]
* Feature Improvements
    - [New features and enhancements]
* Technical Notes
    - [Developer-focused changes]
* Configuration Advice
    - [User guidance for new features]
* Database Changes
    - [Schema changes or "No Changes"]
```

### 7. WordPress Version Compatibility Check
When WordPress releases new versions, verify:
- Minimum WordPress version requirement (currently 5.4)
- Test compatibility with latest WordPress version
- Update "Tested up to" field accordingly
- Check for deprecated WordPress functions in plugin code
- Verify hook compatibility with new WordPress version

### 8. Quality Assurance Steps
Before finalizing version update:
- Ensure all modified files have updated version numbers
- Verify changelog accuracy and completeness
- Check that version increments follow semantic versioning
- Confirm WordPress compatibility statements are accurate
- Review that all breaking changes are documented

### 9. Response Format for Version Updates
When completing version update routine, respond with:
- Version updated from X.Y.Z to A.B.C
- Changelog updated with [number] changes
- WordPress compatibility: [version]
- Files modified: [list of files]

This routine ensures consistent version management and comprehensive change documentation across the TradePress plugin.
