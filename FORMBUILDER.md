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
->rowSpan(10)        // Span multiple grid rows (for side-by-side layouts)
```

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

The `image()` field type provides a complete image upload experience with preview, upload/delete buttons, and a confirmation modal for deletion.

### Basic Usage

```php
MrCatzFormField::image('avatar_file', label: 'Photo')
    ->preview($this->avatarUrl)
    ->fallback($this->name)              // Shows first letter as fallback
    ->onUpload('uploadPhoto')            // Upload button → calls Livewire method
    ->onDelete('deletePhoto', 'Delete this photo?')  // Delete button → confirmation modal
    ->hint('JPG, PNG, WEBP. Max 2MB.')
```

### Preview Styling with `previewClass()`

Full control over preview appearance via Tailwind/DaisyUI classes:

```php
// Circle with ring (like avatar)
->previewClass('w-32 h-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2')

// Large rounded rectangle with border and shadow
->previewClass('w-48 h-48 rounded-lg border-2 border-primary shadow-xl')

// Full width, maintain aspect ratio
->previewClass('w-full aspect-video rounded-lg')

// Square with no rounding
->previewClass('w-64 h-64 rounded-none border border-base-300')

// DaisyUI mask shapes
->previewClass('w-40 h-40 mask mask-squircle')
->previewClass('w-40 h-40 mask mask-hexagon')

// Small, subtle
->previewClass('w-16 h-16 rounded-full shadow-sm')

// Natural image size
->previewClass('max-w-full rounded-lg shadow-md')

// With ring offset and shadow
->previewClass('w-36 h-36 rounded-full ring-4 ring-primary/30 ring-offset-4 ring-offset-base-100 shadow-lg')
```

**Without `previewClass()`** — falls back to pixel `width`/`height` from `preview()` + circle + ring (default 128x128).

### Delete Confirmation Modal

When `->onDelete('method', 'Confirm text?')` is set and a preview exists, the delete button opens a DaisyUI dialog modal with:
- Warning icon
- Custom confirmation text
- "Delete" and "Cancel" buttons

### Side-by-side Layout

Use `->span()` + `->rowSpan()` to place the image beside form fields:

```php
return [
    // Left: form fields (span 8)
    MrCatzFormField::section('Profile Information')->span(8),
    MrCatzFormField::text('name', label: 'Name', icon: 'person')->span(8),
    MrCatzFormField::email('email', label: 'Email', icon: 'mail')->span(8),

    // Right: avatar (span 4, pinned to row 1, spanning all rows)
    MrCatzFormField::image('avatar_file', label: 'Photo')
        ->span(4)->rowSpan(20)
        ->preview($this->avatarUrl)
        ->previewClass('w-32 h-32 rounded-full ring ring-primary ring-offset-2')
        ->fallback($this->name)
        ->onUpload('updateAvatar')
        ->onDelete('deleteAvatar', 'Delete photo?')
        ->hint('JPG, PNG. Max 2MB.'),
];
```

This creates a layout where form fields fill the left 8 columns and the image is pinned to the right 4 columns:

```
┌────── form fields (span 8) ─────┐ ┌── image (span 4) ──┐
│ ▍ Profile Information            │ │   Foto Profil       │
│ [👤] Name ______________________ │ │      ┌──────┐       │
│ [✉] Email ______________________ │ │      │ foto │       │
│                                  │ │      └──────┘       │
│ ▍ Change Password                │ │   [Choose file]     │
│ [🔒] Current Password __________ │ │   [Upload] [🗑]    │
│ [🔒] New Password _____________  │ │   JPG, PNG. 2MB     │
└──────────────────────────────────┘ └─────────────────────┘
```

---

## Grid Layout

Form uses a 12-column CSS grid. Use `->span()` for multi-column layouts:

```php
MrCatzFormField::text('first_name', label: 'First Name')->span(6),
MrCatzFormField::text('last_name', label: 'Last Name')->span(6),
MrCatzFormField::textarea('bio', label: 'Bio'),  // full width (default: span 12)
```

Use `->rowSpan(n)` to make a field span multiple rows (pinned to row 1):

```php
MrCatzFormField::text('name', ...)->span(8),
MrCatzFormField::image('avatar', ...)->span(4)->rowSpan(10),
```

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

---

## Full Example

```php
public $usernameHint = '';
public $avatarUrl;

public function setForm(): array
{
    return [
        // Left column
        MrCatzFormField::section('Account Information')->span(8),
        MrCatzFormField::text('username', label: 'Username', rules: 'required|min:3', icon: 'person')
            ->span(6)->hint($this->usernameHint ?: null, 'success'),
        MrCatzFormField::button('Check', onClick: 'checkUsername', icon: 'search', style: 'info')
            ->withLoading()->span(2),
        MrCatzFormField::email('email', label: 'Email', rules: 'required|email', icon: 'mail')->span(8),

        MrCatzFormField::section('Pricing')->span(8),
        MrCatzFormField::number('price', label: 'Price', rules: 'required|numeric')
            ->prefix('Rp')->suffix('/unit')->style('primary')->span(8),

        MrCatzFormField::divider('Optional')->span(8),
        MrCatzFormField::date('birth_date', label: 'Birth Date')->span(4),
        MrCatzFormField::color('theme_color', label: 'Theme Color')->span(4),
        MrCatzFormField::toggle('is_active', label: 'Active')->span(8)->style('success'),

        // Right column — avatar pinned
        MrCatzFormField::image('avatar_file', label: 'Photo')
            ->span(4)->rowSpan(20)
            ->preview($this->avatarUrl)
            ->previewClass('w-32 h-32 rounded-full ring ring-primary ring-offset-2')
            ->fallback($this->name)
            ->onUpload('updateAvatar')
            ->onDelete('deleteAvatar', 'Delete photo?')
            ->hint('JPG, PNG. Max 2MB.'),
    ];
}

public function checkUsername()
{
    $this->usernameHint = '';
    $this->resetValidation('username');

    if (User::where('username', $this->username)->exists()) {
        $this->addError('username', 'Username already taken!');
    } else {
        $this->usernameHint = '✓ Username available!';
    }
}
```
