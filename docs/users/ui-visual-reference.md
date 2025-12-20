# Users Management UI - Visual Quick Reference

## 🎨 Color Palette Application

### Primary Actions

```
Background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%)
Text: White
Shadow: lg → xl on hover
Transform: scale(1.05) on hover
```

### Secondary Actions

```
Background: #F6211F (solid)
Text: White
Shadow: md → lg on hover
```

### Tertiary Actions

```
Background: #EFEFEF
Text: #4D4B4C
Shadow: md → lg on hover
```

### Input Fields & Dropdowns

```
Border: 2px solid #CF0D0F
Text: #4D4B4C
Focus: outline 2px solid #F6211F
Icon Color: #CF0D0F
```

### Cards & Containers

```
Border: 2px solid #CF0D0F
Background: White
Shadow: md
Border-radius: xl (12px)
```

## 📐 Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│ Users Management Card (border: #CF0D0F)                      │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ Title + Subtitle          [Add][Clear][Export]        │   │
│ │                                                         │   │
│ │ [Search────────────────] [Role───] [Status───]        │   │
│ └───────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Table Card (border: #CF0D0F)                                 │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ USER    │ ROLE │ STATUS │ JOINED │ ACTIONS           │   │
│ ├─────────┼──────┼────────┼────────┼──────────────────┤   │
│ │ ●Name   │ 🏷️   │ 🟢     │ Date   │ [Actions▼]       │   │
│ │ Email   │      │        │        │                   │   │
│ └───────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Pagination Card (border: #CF0D0F)                            │
│ Showing 1 to 10 of 45    [◄][1][2][3][►]                   │
└─────────────────────────────────────────────────────────────┘
```

## 🎯 Button Layout (Single Row)

Desktop View:

```
[🔴 Add User] [🔴 Clear] [⚪ Export PDF]
  Gradient     Solid      Light Gray
  #CF0D0F     #F6211F    #EFEFEF
```

Mobile View (Stacked):

```
[🔴 Add User]
[🔴 Clear]
[⚪ Export PDF]
```

## 🔍 Filter Layout (Single Row)

Desktop:

```
[🔍 Search─────────────────] [Role▼] [Status▼]
     flex-1 (grows)           56px     56px
```

Mobile (Stacked):

```
[🔍 Search───────────]
[Role▼ ──────────────]
[Status▼ ────────────]
```

## 📊 Table Actions Menu

```
┌──────────────────────┐
│ ✏️ Edit User         │ ← #CF0D0F icon
│ 🔄 Change Status     │ ← #F6211F icon
│ 🔑 Reset Password    │ ← #6F6F6F icon
│ 🕐 View Activity     │ ← #4D4B4C icon
└──────────────────────┘
```

## 🎭 Modal Designs

### Create User Modal

```
┌────────────────────────────────┐
│ Create New User           [×]  │
├────────────────────────────────┤
│ Name: [____________]           │
│ Email: [____________]          │
│ Password: [____________]       │
│ Role: [Select─▼]              │
│                                │
│ [Cancel]    [👤 Create User]  │
│  #EFEFEF      Gradient         │
└────────────────────────────────┘
```

### Edit User Modal

```
┌────────────────────────────────┐
│ Edit User                 [×]  │
├────────────────────────────────┤
│ Name: [____________]           │
│ Email: [____________]          │
│ Role: [Select─▼]              │
│                                │
│ [Cancel]    [✓ Update User]   │
│  #EFEFEF      Gradient         │
└────────────────────────────────┘
```

### Status Change Modal

```
┌────────────────────────────────┐
│ Change User Status             │
├────────────────────────────────┤
│ [Active     🟢]               │
│ [Suspended  🟡]               │
│ [Inactive   🔴]               │
│                                │
│ [× Cancel]                     │
│   #EFEFEF                      │
└────────────────────────────────┘
Border: 3px solid #CF0D0F
```

## 🌈 State Variations

### Loading

```
     ⚪ ← Spinning circle (#CF0D0F)
  Loading users...
     (#6F6F6F)
```

### Empty

```
     👥 ← Icon in gradient circle
  No users found
  Get started by creating...
  [🔴 Add First User]
```

### Hover Effects

```
Button: scale(1.05) + shadow-xl
Row: background-color: gray-50
Menu Item: background-color: #EFEFEF
```

## 📱 Responsive Breakpoints

- **Mobile** (< 640px): Stacked layout, full-width buttons
- **Tablet** (640px - 1024px): Mixed layout, some horizontal
- **Desktop** (> 1024px): Full horizontal layout, optimal spacing

## ⚡ Performance Features

1. **Debounced Search**: 500ms delay to reduce API calls
2. **Smooth Scrolling**: Hardware-accelerated transitions
3. **Optimized Fonts**: Antialiasing for crisp text
4. **Will-change**: Hint browser for animations
5. **Efficient Pagination**: Smart page number display

## 🎨 Icon Reference

| Action        | Icon              | Color             |
| ------------- | ----------------- | ----------------- |
| Add User      | PlusIcon          | White on gradient |
| Clear Filters | XMarkIcon         | White on #F6211F  |
| Export        | ArrowDownTrayIcon | #4D4B4C           |
| Edit          | PencilIcon        | #CF0D0F           |
| Status        | ArrowPathIcon     | #F6211F           |
| Password      | KeyIcon           | #6F6F6F           |
| Activity      | ClockIcon         | #4D4B4C           |
| Empty         | UserGroupIcon     | #6F6F6F           |

## 🎯 Design Principles Applied

1. **Consistency**: Same colors for same actions
2. **Hierarchy**: Gradient > Solid > Light backgrounds
3. **Accessibility**: High contrast, clear focus states
4. **Feedback**: Hover, active, and loading states
5. **Efficiency**: Single-row layouts, grouped actions
6. **Clarity**: Icons + text for all actions
7. **Performance**: Optimized CSS, minimal repaints

## 🚀 Quick Copy-Paste Styles

### Primary Button

```css
background: linear-gradient(135deg, #cf0d0f 0%, #f6211f 100%);
border-radius: 0.5rem; /* 8px */
padding: 0.625rem 1.25rem; /* 10px 20px */
font-weight: 700;
color: white;
box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
transition: all 200ms;
```

### Input Field

```css
border: 2px solid #cf0d0f;
border-radius: 0.5rem;
padding: 0.75rem 1rem;
color: #4d4b4c;
font-weight: 500;
```

### Card Container

```css
border: 2px solid #cf0d0f;
border-radius: 0.75rem; /* 12px */
background: white;
box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
padding: 1.5rem; /* 24px */
```
