# 🎯 Pharma Group Feature - How to Access

## Where to Find It in the UI

### Step 1: Navigate to Organization Setup

1. Log in to Avenzur ERP
2. Click on **Settings** (top menu or sidebar)
3. Click on **Organization Setup** (or "Setup Organization")

### Step 2: Find the Pharma Groups Tab

You will see a tabbed interface with:

```
┌─────────────────────────────────────────────────────────┐
│  [Pharma Groups] [Pharmacies] [Branches] [Hierarchy]    │
└─────────────────────────────────────────────────────────┘
    ↑
    Click here (First Tab - now with Building Icon)
```

### Step 3: View the Pharma Groups Page

```
┌────────────────────────────────────────────────────────────┐
│                    MANAGE PHARMA GROUPS                   │
│                                                            │
│ This module manages pharmacy group (company) hierarchy    │
│                                                            │
│                  [+ Add Pharma Group]                      │
│                                                            │
├────────────────────────────────────────────────────────────┤
│ Code   │ Name    │ Address │ Phone │ Email │ Actions     │
├────────────────────────────────────────────────────────────┤
│ PG-001 │ Main Gp │ Address │ Phone │ Email │ [✏️] [🗑️] │
│ PG-002 │ Branch  │ Address │ Phone │ Email │ [✏️] [🗑️] │
└────────────────────────────────────────────────────────────┘
```

## Creating Your First Pharma Group

### Click "Add Pharma Group"

A beautiful modal will appear:

```
╔═══════════════════════════════════════════════════════════╗
║                 ADD PHARMA GROUP                          ║
║  ╔─────────────────────┐  ╔─────────────────────────┐   ║
║  │ Pharma Group Info   │  │ Location Details        │   ║
║  ├─────────────────────┤  ├─────────────────────────┤   ║
║  │ Code:        [_____]│  │ Address:                │   ║
║  │ Name:        [_____]│  │ ┌───────────────────┐   │   ║
║  │ Phone:       [_____]│  │ │                   │   │   ║
║  │ Email:       [_____]│  │ │                   │   │   ║
║  │              │  │ └───────────────────┘   │   ║
║  │              │  │ Country: Saudi Arabia   │   ║
║  └─────────────────────┘  └─────────────────────────┘   ║
║                                                           ║
║              [Cancel]           [Add Pharma Group]       ║
╚═══════════════════════════════════════════════════════════╝
```

### Fill in the Information

| Field   | Example             | Required   |
| ------- | ------------------- | ---------- |
| Code    | PG-001              | ✓ Yes      |
| Name    | Main Pharmacy Group | ✓ Yes      |
| Phone   | +966 50 123 4567    | ✓ Yes      |
| Email   | info@group.com      | ✗ Optional |
| Address | 123 Main St, Riyadh | ✓ Yes      |

### Click "Add Pharma Group"

The system will:

1. ✅ Validate your input
2. ✅ Create warehouse entry
3. ✅ Create company record
4. ✅ Create pharmacy group record
5. ✅ Show success message
6. ✅ Reload the table with your new group

## Editing a Pharma Group

### Click the Edit Button (Pencil Icon)

```
Table Row: PG-001 | Main Group | ... | [✏️] [🗑️]
                                       ↑ Click here
```

The edit modal opens with all your data pre-filled:

```
╔═══════════════════════════════════════════════════════════╗
║              EDIT PHARMA GROUP                            ║
║  ╔─────────────────────┐  ╔─────────────────────────┐   ║
║  │ Pharma Group Info   │  │ Location Details        │   ║
║  ├─────────────────────┤  ├─────────────────────────┤   ║
║  │ Code:        [PG-001]│  │ Address:                │   ║
║  │ Name:        [Prelo]│  │ ┌─────────────────────┐ │   ║
║  │ Phone:       [+966..] │  │ │ 123 Main St, Riyadh │ │   ║
║  │ Email:       [info@..] │  │ └─────────────────────┘ │   ║
║  │              │  │ Country: Saudi Arabia   │   ║
║  └─────────────────────┘  └─────────────────────────┘   ║
║                                                           ║
║              [Cancel]          [Update Pharma Group]      ║
╚═══════════════════════════════════════════════════════════╝
```

### Modify Fields

Change any field you want to update, then click "Update Pharma Group"

Success! Your changes are saved instantly.

## Deleting a Pharma Group

### Click the Delete Button (Trash Icon)

```
Table Row: PG-001 | Main Group | ... | [✏️] [🗑️]
                                            ↑ Click here
```

### Confirm Deletion

A warning dialog appears:

```
╔═══════════════════════════════════════════════════════════╗
║                    DELETE PHARMA GROUP?                  ║
║                                                           ║
║ Are you sure you want to delete "Main Pharmacy Group"    ║
║ and all associated pharmacies and branches?              ║
║                                                           ║
║ This action cannot be undone!                            ║
║                                                           ║
║                  [Cancel]        [Delete]                ║
╚═══════════════════════════════════════════════════════════╝
```

### Click "Delete" to Confirm

The system will:

1. Delete the pharma group
2. Delete all pharmacies under it
3. Delete all branches under those pharmacies
4. Delete all warehouse entries
5. Show success message
6. Reload the table

## What Gets Deleted?

When you delete a Pharma Group, the following are also deleted:

```
Deleting: Pharma Group "Main Group"
  └─ Includes:
     ├─ All Pharmacies under this group (10 pharmacies)
     │  └─ All Branches under those pharmacies (25 branches)
     │     └─ All Warehouse entries for those branches
     ├─ All Loyalty Records
     └─ All Company Records
```

**Total Impact**: 1 group + 10 pharmacies + 25 branches = 36 records

---

## Real-Time Features

### ✨ Smooth Animations

- Modals fade in/out smoothly
- Alerts slide in from top
- Button feedback on click

### ⚡ Instant Updates

- No page refresh needed
- Table updates in real-time
- Forms clear automatically
- Dropdowns update instantly

### 🔔 Helpful Alerts

After each action, you'll see:

- ✅ **Success**: Green alert with checkmark
- ❌ **Error**: Red alert with details
- ⚠️ **Warning**: Orange alert before delete

```
Example Success Alert:
╔═══════════════════════════════════════╗
║ ✓ SUCCESS                              ║
║ Pharmacy Group created successfully   ║
╚═══════════════════════════════════════╝
```

---

## Keyboard Shortcuts

| Shortcut         | Action             |
| ---------------- | ------------------ |
| Click Code field | Auto-focus         |
| Tab              | Move to next field |
| Enter            | Submit form        |
| Escape           | Close modal        |

---

## Tips & Tricks

### 💡 Pro Tips

1. **Use Meaningful Codes**: PG-001, PG-002 (easy to remember)
2. **Include Location in Name**: "Main Pharmacy Group - Riyadh"
3. **Keep Consistent Format**: All codes should follow same pattern
4. **Backup Important Groups**: Export before bulk deletes
5. **Test First**: Create test group before production

### ⚠️ Important Notes

- **Code must be unique** - Can't have duplicate codes
- **Name must be unique** - Can't have duplicate names
- **Deletion is permanent** - Deleted data cannot be recovered
- **Affects pharmacies** - Deleting group deletes all its pharmacies
- **Database transaction** - All-or-nothing operation

---

## Troubleshooting

### Problem: Form not submitting

**Solution**:

- Check all required fields are filled
- Look for red error messages
- Verify code is unique
- Try refreshing page if stuck

### Problem: Modal not opening

**Solution**:

- Check browser console for errors
- Try clearing browser cache
- Use modern browser (Chrome, Firefox, Safari)
- Disable ad blockers

### Problem: Table not updating

**Solution**:

- Wait a moment (AJAX may be processing)
- Try refreshing page
- Check browser console for errors
- Verify internet connection

### Problem: Delete didn't work

**Solution**:

- Check confirmation was clicked
- Look for error messages
- Try again
- Check if group already deleted

---

## Screen Capture Examples

### Tab Navigation

```
Organization Setup Page
┌─────────────────────────────────────────────────────┐
│  Settings > Organization Setup                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│  [🏢 Pharma Groups] [🏥 Pharmacies] [📍 Branches]  │
│   ↑ Active (Green background)                       │
│                                                      │
│  Content for Pharma Groups tab displays here        │
│                                                      │
└─────────────────────────────────────────────────────┘
```

### Add Button

```
┌─────────────────────────────────────────────────────┐
│                                                      │
│  [✚ Add Pharma Group]                               │
│   ↑ Green success button                             │
│                                                      │
│  Click to open the add modal                        │
│                                                      │
└─────────────────────────────────────────────────────┘
```

### Table with Actions

```
┌──────────────────────────────────────────────────────────────┐
│ Code   │ Name              │ Address     │ Phone │ Actions   │
├──────────────────────────────────────────────────────────────┤
│ PG-001 │ Main Pharmacy Gp  │ Riyadh Ctr  │ +966  │ ✏️ 🗑️    │
│        │                   │             │ 50123 │ ↑  ↑     │
│        │                   │             │ 4567  │ Edit Delete│
├──────────────────────────────────────────────────────────────┤
│ PG-002 │ Secondary Group   │ Jeddah      │ +966  │ ✏️ 🗑️    │
│        │                   │ Downtown    │ 50234 │           │
├──────────────────────────────────────────────────────────────┤
│ PG-003 │ Branch Services   │ Dammam Plz  │ +966  │ ✏️ 🗑️    │
│        │                   │             │ 50345 │           │
└──────────────────────────────────────────────────────────────┘
```

---

## Frequently Asked Questions

**Q: Can I have two pharma groups with the same code?**  
A: No. Each code must be unique across all groups.

**Q: What happens if I delete a pharma group?**  
A: All pharmacies and branches under it are also deleted. This is permanent.

**Q: Can I edit the country?**  
A: The UI shows Saudi Arabia (8) as default. Contact admin to change.

**Q: Are there limits on how many groups I can create?**  
A: No technical limits, but database performance may vary with large numbers.

**Q: Can I export the groups?**  
A: Not yet, but this is planned for future versions.

**Q: Is there an undo option?**  
A: No. Use database backups if you need to recover deleted data.

---

## Next Steps After Creating Groups

1. **Create Pharmacies** - Go to Pharmacies tab and create under your group
2. **Create Branches** - Go to Branches tab and create under pharmacies
3. **Assign Budgets** - Go to Budget Setup and allocate budgets to groups
4. **Configure Loyalty** - Set up loyalty programs for the group
5. **Add Users** - Assign users to groups for permission management

---

**Version**: 1.0  
**Last Updated**: October 29, 2025  
**Status**: ✅ Live and Ready to Use

For detailed technical documentation, see:

- `.github/PHARMA_GROUP_FEATURE.md` - Full technical details
- `.github/PHARMA_GROUP_QUICK_REF.md` - Developer reference
- `PHARMA_GROUP_README.md` - Complete guide
