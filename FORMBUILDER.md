# MrCatz Form Builder

Define form fields programmatically in your Page component — no separate Blade form file needed. **Fully optional** — existing Blade forms (`@yield('forms')`) continue to work.

## Basic Usage

```php
use MrCatz\DataTable\MrCatzFormField;

class UserPage extends MrCatzComponent
{
    public $name, $email, $role, $password, $password_confirmation;

    public function setForm(): array
    {
        return [
            MrCatzFormField::text('name', label: 'Name', rules: 'required|max:255', icon: 'person'),
            MrCatzFormField::email('email', label: 'Email', rules: 'required|email', icon: 'mail'),
            MrCatzFormField::select('role', label: 'Role', data: [
                ['id' => 'admin', 'name' => 'Admin'],
                ['id' => 'user', 'name' => 'User'],
            ], value: 'id', option: 'name'),
            MrCatzFormField::password('password', label: 'Password', rules: 'required|min:8')
                ->withConfirmation(),
        ];
    }

    public function saveData()
    {
        $this->validate(
            $this->getFormValidationRules(),
            $this->getFormValidationMessages()
        );
        // ... save logic
    }
}
```

When `setForm()` returns fields, the form modal auto-renders them. If `setForm()` is not overridden (returns `[]`), the Blade `@yield('forms')` approach is used — **backward compatible**.

---

## Field Types

### Input Fields

| Method | Description |
|---|---|
| `text(id, label, ...)` | Text input |
| `email(id, label, ...)` | Email input |
| `password(id, label, ...)` | Password input |
| `number(id, label, ...)` | Number input with `step`, `min`, `max` |
| `url(id, label, ...)` | URL input |
| `tel(id, label, ...)` | Phone number input |
| `search(id, label, ...)` | Search input (default icon: search) |
| `date(id, label, ...)` | Date picker |
| `time(id, label, ...)` | Time picker |
| `datetime(id, label, ...)` | Date & time picker |
| `color(id, label, ...)` | Color picker |
| `hidden(id)` | Hidden input |

### Selection Fields

| Method | Description |
|---|---|
| `select(id, label, data, value, option, ...)` | Dropdown select |
| `radio(id, label, options)` | Radio buttons (`options: ['val' => 'Label']`) |
| `toggle(id, label)` | Checkbox toggle (switch) |
| `checkbox(id, label)` | Single checkbox (square) |
| `chooser(id, label, data, value, option)` | Multi-checkbox buttons |
| `rating(id, label, max)` | Star rating (default: 5 stars) |
| `range(id, label, min, max, step)` | Range slider with min/max labels |

### Text Fields

| Method | Description |
|---|---|
| `textarea(id, label, ...)` | Multi-line text |

### File & Image

| Method | Description |
|---|---|
| `file(id, label, ...)` | File upload with optional `accept` |
| `image(id, label, ...)` | Image upload with preview, upload/delete buttons, fallback |

### Button

| Method | Description |
|---|---|
| `button(label, onClick, icon?, style?)` | Action button with Livewire method hook |

```php
MrCatzFormField::button('Check Username', onClick: 'checkUsername', icon: 'search', style: 'info')
    ->withLoading()  // Show spinner while method runs
    ->span(4)
```

### Static Content

| Method | Description |
|---|---|
| `section(title)` | Section heading with border |
| `note(text)` | Small muted text |
| `alert(text, type)` | Alert box: `'info'`, `'warning'`, `'success'`, `'error'` |
| `html(content)` | Raw HTML block |
| `divider(text?)` | Horizontal divider, optional text in center |

---

## Modifiers (Chainable)

### Layout

```php
->span(6)           // Grid column span (1-12, default: 12)
->rowSpan(10)        // Span multiple grid rows (for side-by-side layouts, pinned to row 1)
->mobileOrder(-1)    // Visual order on mobile only (< 640px). Negative = appear first
```

**Form gap** — set on your component:

```php
public string $formGap = '1rem';   // default, equivalent to Tailwind gap-4
// Other values: '0.5rem', '1.25rem', '1.5rem', '2rem'
```

**Responsive:** On mobile (< 640px), all fields automatically become full-width and `rowSpan` is reset. Use `mobileOrder()` to control which field appears first on mobile.

### Content

```php
->hint('Helper text')                    // Helper text below field (default gray)
->hint('Available!', 'success')          // Colored hint: success, error, warning, info
->prefix('Rp')                           // Text before input
->suffix('kg')                           // Text after input
```

### Style & Size (DaisyUI)

```php
->style('primary')   // Adds input-primary, select-primary, textarea-primary, etc.
->size('lg')         // Adds input-lg, select-lg, textarea-lg, etc.
```

| Style values | `primary`, `secondary`, `accent`, `info`, `success`, `warning`, `error`, `ghost`, `neutral` |
|---|---|
| **Size values** | `xs`, `sm`, `md`, `lg`, `xl` |

### Wire Model

```php
->live()             // wire:model.live (realtime update)
->lazy()             // wire:model.blur (update on blur)
->debounce(300)      // wire:model.live.debounce.300ms
```

### Visibility & State

```php
->disabled()                 // Always disabled
->disabled(fn() => ...)      // Conditional disable
->hideWhen(fn() => ...)      // Conditional hide
->visibleWhen('field', 'value')              // Show when field == value
->visibleWhen('field', ['a', 'b'])           // Show when field is 'a' or 'b'
->visibleWhenAll(['type' => 'x', 'role' => 'admin'])  // All conditions must match
```

### Events

```php
->onChange('methodName')     // Call Livewire method on change
->dependsOn('other_field')  // Re-render when parent field changes
```

### Button-specific

```php
->withLoading()              // Show spinner during method execution
->withLoading('targetMethod') // Targeted loading via wire:target
```

### Password-specific

```php
->withConfirmation(label: 'Confirm Password')  // Auto-generates confirmation field + 'confirmed' rule
```

### File-specific

```php
->preview($url)              // Show current file preview
->preview($url, width: 128, height: 128)  // With pixel dimensions (for file type)
```

---

## Image Field

The `image()` field type provides a complete image upload experience: preview with lightbox zoom, upload/delete buttons, delete confirmation modal, and fallback initial letter.

### Basic Usage

```php
MrCatzFormField::image('avatar_file', label: 'Photo')
    ->preview($this->avatarUrl, width: 128, height: 128)  // URL + pixel size
    ->previewClass('rounded-full ring ring-primary ring-offset-2')  // shape/decoration
    ->fallback($this->name)              // First letter as fallback when no image
    ->onUpload('uploadPhoto')            // Upload button → calls Livewire method
    ->onDelete('deletePhoto', 'Delete this photo?')  // Delete → confirmation modal
    ->hint('JPG, PNG, WEBP. Max 2MB.')
```

### Preview Size & Shape

Size and shape are controlled separately:

- **`preview(url, width, height)`** — pixel dimensions via inline style (default: 128x128). Immune to CSS resets.
- **`previewClass(class)`** — shape & decoration via Tailwind classes (default: circle + ring).

```php
// Circle avatar
->preview($url, width: 128, height: 128)
->previewClass('rounded-full ring ring-primary ring-offset-2')

// Large rounded rectangle
->preview($url, width: 200, height: 200)
->previewClass('rounded-lg border-2 border-primary shadow-xl')

// Small circle, no ring
->preview($url, width: 64, height: 64)
->previewClass('rounded-full shadow-sm')

// DaisyUI mask shape
->preview($url, width: 160, height: 160)
->previewClass('mask mask-squircle')

// Square, no rounding
->preview($url, width: 256, height: 256)
->previewClass('rounded-none border border-base-300')
```

### Image Lightbox (Click to Zoom)

Clicking the image preview opens a fullscreen modal with zoom controls:

- **Zoom in/out** — `+` / `-` buttons (25% increments, range 50%–300%)
- **Percentage display** — shows current zoom level
- **Reset** — button to return to 100%
- **Close** — X button, backdrop click, or Escape key
- Smooth transition animation on zoom

Lightbox is automatic — no configuration needed. Only active when a preview image exists.

### Delete Confirmation Modal

When `->onDelete('method', 'Confirm text?')` is set and a preview exists, the delete button opens a DaisyUI dialog modal with warning icon, custom confirmation text, and Delete/Cancel buttons.

### Side-by-side Layout

Use `->span()` + `->rowSpan()` + `->mobileOrder()`:

```php
return [
    // Left: avatar (span 4, pinned row 1, appear first on mobile)
    MrCatzFormField::image('avatar_file', label: 'Photo')
        ->span(4)->rowSpan(20)->mobileOrder(-1)
        ->preview($this->avatarUrl, width: 128, height: 128)
        ->previewClass('rounded-full ring ring-primary ring-offset-2')
        ->fallback($this->name)
        ->onUpload('updateAvatar')
        ->onDelete('deleteAvatar', 'Delete photo?')
        ->hint('JPG, PNG. Max 2MB.'),

    // Right: form fields (span 8)
    MrCatzFormField::section('Profile Information')->span(8),
    MrCatzFormField::text('name', label: 'Name', icon: 'person')->span(8),
    MrCatzFormField::email('email', label: 'Email', icon: 'mail')->span(8),
];
```

**Desktop (> 640px):**
```
┌── image (span 4) ──┐ ┌────── form (span 8) ─────┐
│    ┌──────┐         │ │ ▍ Profile Information     │
│    │ foto │ (click  │ │ [Name] __________________ │
│    │128x128│ zoom)  │ │ [Email] _________________ │
│    └──────┘         │ │ ▍ Change Password         │
│   [Choose file]     │ │ [Password] ______________ │
│   [Upload] [🗑]    │ └───────────────────────────┘
└─────────────────────┘
```

**Mobile (< 640px):** — image moves to top via `mobileOrder(-1)`
```
┌──────── full width ────────┐
│ Photo (order: -1 = first)  │
│ [foto] [upload] [🗑]       │
├─────────────────────────────┤
│ Profile Information         │
│ Name, Email, Password...    │
└─────────────────────────────┘
```

---

## Grid Layout

Form uses a 12-column CSS grid.

### Column Span

```php
MrCatzFormField::text('first_name', label: 'First Name')->span(6),
MrCatzFormField::text('last_name', label: 'Last Name')->span(6),
MrCatzFormField::textarea('bio', label: 'Bio'),  // full width (default: span 12)
```

### Row Span (Side-by-side)

Pin a field to row 1 and span multiple rows:

```php
MrCatzFormField::image('avatar', ...)->span(4)->rowSpan(20),
MrCatzFormField::text('name', ...)->span(8),
MrCatzFormField::email('email', ...)->span(8),
```

### Mobile Order

Control which fields appear first on mobile (< 640px). On mobile, all fields become full-width and `rowSpan` is reset.

```php
->mobileOrder(-1)    // Appear first on mobile
->mobileOrder(0)     // Default order
->mobileOrder(10)    // Appear last on mobile
```

### Form Gap

Set the spacing between fields on your component:

```php
public string $formGap = '1rem';     // Default (Tailwind gap-4 equivalent)
public string $formGap = '0.5rem';   // Compact
public string $formGap = '1.5rem';   // Spacious
public string $formGap = '2rem';     // Very spacious
```

### Responsive Behavior

The form grid is automatically responsive:
- **Desktop (> 640px)** — `span()` and `rowSpan()` layout applies
- **Mobile (< 640px)** — all fields become full-width, `rowSpan` resets, `mobileOrder` controls visual order

---

## Dynamic / Dependent Fields

```php
// Radio toggle — show/hide fields based on selection
MrCatzFormField::radio('type', label: 'Type', options: ['url' => 'URL', 'file' => 'FILE']),
MrCatzFormField::text('file_url', label: 'URL', icon: 'link')
    ->visibleWhen('type', 'url'),
MrCatzFormField::file('file_file', label: 'File')
    ->visibleWhen('type', 'file'),

// Cascade select — parent updates child data
MrCatzFormField::select('province_id', label: 'Province', data: $this->provinces, value: 'id', option: 'name')
    ->live()->onChange('loadCities'),
MrCatzFormField::select('city_id', label: 'City', data: $this->cities, value: 'id', option: 'name')
    ->dependsOn('province_id'),
```

In your component:

```php
public function loadCities($value)
{
    $this->cities = City::where('province_id', $value)->get()->toArray();
    $this->city_id = null;
}
```

---

## Validation

Rules are defined on each field — error messages render automatically:

```php
MrCatzFormField::text('name', label: 'Name',
    rules: 'required|max:255',
    messages: [
        'required' => 'Name is required!',
        'max' => 'Name must be under :max characters',
    ]
),
```

In `saveData()`:

```php
$this->validate(
    $this->getFormValidationRules(),     // ['name' => 'required|max:255', ...]
    $this->getFormValidationMessages()   // ['name.required' => 'Name is required!', ...]
);
```

---

## Icons

Form field icons support 3 modes:

```php
// 1. Icon name — uses mrcatz_icon() / config form_icons
MrCatzFormField::text('name', icon: 'person'),

// 2. Raw HTML — rendered as-is
MrCatzFormField::text('name', icon: '<i class="bi bi-person"></i>'),

// 3. Registered in config/mrcatz.php form_icons
// 'form_icons' => ['person' => '<path d="..."/>' ]
MrCatzFormField::text('name', icon: 'person'),
```

Config `form_icons` values can be SVG paths (auto-wrapped in `<svg>`) or raw HTML:

```php
// config/mrcatz.php
'form_icons' => [
    'person' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75..."/>',
    'mail'   => '<i class="bi bi-envelope"></i>',
],
```

---

## Standalone Form (Outside DataTable)

Form Builder works on **any Livewire component** — not just DataTable pages. Use the `HasFormBuilder` trait:

```php
use Livewire\Component;
use MrCatz\DataTable\Concerns\HasFormBuilder;
use MrCatz\DataTable\MrCatzFormField;

class ProfilePage extends Component
{
    use HasFormBuilder;

    public $name, $email;

    public function setForm(): array
    {
        return [
            MrCatzFormField::text('name', label: 'Name', rules: 'required', icon: 'person'),
            MrCatzFormField::email('email', label: 'Email', rules: 'required|email', icon: 'mail'),
        ];
    }

    public function save()
    {
        $this->validate($this->getFormValidationRules(), $this->getFormValidationMessages());
        // save logic...
    }

    public function render() { return view('profile'); }
}
```

In your Blade view:

```blade
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        @include('mrcatz::components.ui.form-standalone', [
            'submitMethod' => 'save',
            'submitLabel'  => 'Save Changes',
            'submitIcon'   => 'check_circle',
            'cancelUrl'    => route('dashboard'),
        ])
    </div>
</div>
```

Or include just the fields for full layout control:

```blade
@include('mrcatz::components.ui.form-builder')
<button class="btn btn-primary mt-4" wire:click="save">Save</button>
```

### Notifications

When extending `MrCatzComponent`, use **`$this->notice()`** for toast notifications on standalone pages:

```php
$this->notice('success', 'Profile updated!');
$this->notice('error', 'Something went wrong.');
$this->notice('warning', 'Check your input.');
$this->notice('info', 'Processing...');
```

> **Note:** `show_notif()` only works on pages with DataTable JS. For standalone pages (profile, settings, etc.), always use `notice()` instead — it dispatches the `notice` browser event that the notification component listens for.

---

## Full Example

Profile edit page with avatar upload, username check, password change — all via Form Builder:

```php
class ProfilePage extends MrCatzComponent
{
    use WithFileUploads;

    public $name, $username, $email, $avatarUrl;
    public $current_password, $password, $password_confirmation;
    public $avatar_file, $usernameHint = '', $oldUsername;

    public string $formGap = '1.25rem';

    public function setForm(): array
    {
        return [
            // Left: avatar (click to zoom, upload, delete with modal confirm)
            MrCatzFormField::image('avatar_file', label: 'Photo')
                ->span(4)->rowSpan(20)->mobileOrder(-1)
                ->preview($this->avatarUrl, width: 128, height: 128)
                ->previewClass('rounded-full ring ring-primary ring-offset-2')
                ->fallback($this->name)
                ->onUpload('updateAvatar')
                ->onDelete('deleteAvatar', 'Delete photo?')
                ->hint('JPG, PNG. Max 2MB.'),

            // Right: form fields
            MrCatzFormField::section('Account Information')->span(8),
            MrCatzFormField::text('name', label: 'Name', rules: 'required', icon: 'person')->span(8),
            MrCatzFormField::text('username', label: 'Username', rules: 'required|min:3', icon: 'alternate_email')
                ->span(6)->hint($this->usernameHint ?: null, 'success'),
            MrCatzFormField::button('Check', onClick: 'checkUsername', icon: 'search', style: 'info')
                ->withLoading()->span(2),
            MrCatzFormField::email('email', label: 'Email', rules: 'required|email', icon: 'mail')->span(8),

            MrCatzFormField::section('Change Password')->span(8),
            MrCatzFormField::note('Leave empty to keep current password.')->span(8),
            MrCatzFormField::password('current_password', label: 'Current Password', icon: 'lock')->span(8),
            MrCatzFormField::password('password', label: 'New Password', icon: 'lock')
                ->span(8)->withConfirmation(label: 'Confirm New Password'),
        ];
    }

    public function checkUsername()
    {
        $this->usernameHint = '';
        $this->resetValidation('username');

        if ($this->username === $this->oldUsername) {
            $this->usernameHint = '✓ Username unchanged.';
            return;
        }
        if (User::where('username', $this->username)->exists()) {
            $this->addError('username', 'Username already taken!');
        } else {
            $this->usernameHint = '✓ Username available!';
        }
    }

    public function save()
    {
        $this->validate($this->getFormValidationRules(), $this->getFormValidationMessages());
        // ... save logic
        $this->notice('success', 'Profile updated!');  // use notice() for standalone pages
    }
}
```
