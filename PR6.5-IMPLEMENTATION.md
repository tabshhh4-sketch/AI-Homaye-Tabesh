# PR 6.5 - Core Integration: Event Bus Implementation

## Overview

This PR implements a centralized Event Bus system that enables seamless communication between:
- AI Core (Gemini)
- React Sidebar
- DOM Elements (Shortcodes, Forms)
- Visual Feedback Systems

## Architecture

```
┌─────────────────────────────────────────────────┐
│           window.Homa (Global Event Bus)        │
│  ┌───────────────────────────────────────────┐  │
│  │  State: {                                 │  │
│  │    isSidebarOpen: bool,                   │  │
│  │    currentUserInput: {},                  │  │
│  │    pageMap: {},                           │  │
│  │    indexerReady: bool,                    │  │
│  │    aiProcessing: bool                     │  │
│  │  }                                        │  │
│  └───────────────────────────────────────────┘  │
│                                                   │
│  Methods:                                         │
│  - emit(eventName, data)                         │
│  - on(eventName, callback)                       │
│  - off(eventName, callback)                      │
│  - updateState(updates)                          │
│  - checkConnectivity()                           │
└─────────────────────────────────────────────────┘
           │                    │                   │
           ▼                    ▼                   ▼
┌──────────────────┐  ┌──────────────┐  ┌──────────────┐
│   Indexer        │  │   React      │  │  Command     │
│   (Vanilla JS)   │  │   Sidebar    │  │  Interpreter │
└──────────────────┘  └──────────────┘  └──────────────┘
```

## Key Components

### 1. Event Bus (`homa-event-bus.js`)

**Purpose**: Central pub/sub system for all Homa components

**Key Features**:
- Namespaced events (`homa:*`)
- Global state management
- Event history for debugging
- Connectivity checking
- Cleanup functions for memory management

**Usage Example**:
```javascript
// Emit an event
window.Homa.emit('site:input_change', {
    field: 'tirage',
    value: '500',
    meaning: 'تیراژ'
});

// Listen to an event
const cleanup = window.Homa.on('site:input_change', (data) => {
    console.log('Field changed:', data);
});

// Cleanup when done
cleanup();
```

### 2. Command Interpreter (`homa-command-interpreter.js`)

**Purpose**: Parse JSON commands from Gemini AI and execute them

**Supported Commands**:
- `HIGHLIGHT` - Highlight an element
- `SQUEEZE_LAYOUT` - Open sidebar
- `SCROLL_TO` - Scroll to element
- `SHOW_TOOLTIP` - Display tooltip
- `CLICK` - Click element
- `FILL_FIELD` - Fill form field

**Usage Example**:
```javascript
// Emit a command
window.Homa.emit('ai:command', {
    action_type: 'ui_interaction',
    command: 'HIGHLIGHT',
    target_selector: '.et_pb_button_0'
});
```

### 3. React Bridge (`homaReactBridge.js`)

**Purpose**: Connect React components to the Event Bus

**React Hooks**:
- `useHomaEvent(eventName, callback)` - Subscribe to events
- `useHomaEmit()` - Get emit function
- `useHomaState()` - Access global state
- `useSiteInputChanges(callback)` - Listen for form changes
- `useAICommand()` - Send commands
- `useAIResponse(callback)` - Listen for AI responses

**Usage Example**:
```jsx
import { useHomaEvent, useHomaEmit } from '../homaReactBridge';

function MyComponent() {
    const emit = useHomaEmit();
    
    useHomaEvent('site:input_change', (data) => {
        console.log('Site changed:', data);
    });
    
    return <button onClick={() => emit('custom:event', {})}>
        Click me
    </button>;
}
```

### 4. Updated Indexer

**Changes**:
- Emits `indexer:ready` when scanning complete
- Emits `site:input_change` when form fields change
- Integrates with Event Bus
- Auto-attaches change listeners to indexed fields

**Events Emitted**:
- `indexer:ready` - When page scan completes
- `site:input_change` - When any indexed field changes

### 5. Updated Orchestrator

**Changes**:
- Emits `sidebar:opened` / `sidebar:closed`
- Updates global state on open/close
- Integrates with Event Bus

**Events Emitted**:
- `sidebar:opened` - When sidebar opens
- `sidebar:closed` - When sidebar closes

### 6. Updated HomaSidebar (React)

**Changes**:
- Uses React Bridge hooks
- Listens for `site:input_change` events
- Emits `chat:user_message` when user sends message
- Emits `ai:processing` state changes
- Emits `ai:command` for each AI action

**Events Emitted**:
- `react:ready` - When React loads
- `chat:user_message` - When user sends message
- `ai:processing` - AI processing state
- `ai:command` - AI commands to execute

**Events Listened**:
- `site:input_change` - Form field changes
- `ai:processing` - AI state updates
- `ai:response_received` - AI responses

## Event Flow Examples

### Example 1: User Changes Form Field

```
User types in field
       │
       ▼
Indexer detects change (debounced 300ms)
       │
       ▼
Emit: homa:site:input_change
       │
       ├──────────────────────┐
       ▼                      ▼
React Sidebar receives    Command Interpreter
   (optional action)          (optional action)
```

### Example 2: AI Sends Command

```
AI generates response
       │
       ▼
React calls API endpoint
       │
       ▼
Emit: homa:ai:response_received
       │
       ▼
Command Interpreter receives
       │
       ▼
Parse and execute command
       │
       ▼
Emit: homa:command:executed
```

### Example 3: Sidebar Opens

```
User clicks FAB button
       │
       ▼
HomaOrchestrator.openSidebar()
       │
       ├──────────────────────┬────────────────┐
       ▼                      ▼                ▼
Update global state    Emit sidebar:opened   Dispatch CustomEvent
       │                      │                │
       ▼                      ▼                ▼
React updates         Indexer notified    CSS animation
```

## Testing & Validation

### 1. Connectivity Check

Open browser console and run:

```javascript
window.Homa.checkConnectivity()
```

Expected output:
```javascript
{
    timestamp: 1234567890,
    eventBus: true,
    indexer: true,
    orchestrator: true,
    uiExecutor: true,
    reactSidebar: true,
    listeners: {
        'indexer:ready': 1,
        'site:input_change': 2,
        // ... more listeners
    },
    state: { /* current state */ },
    testPassed: true
}
```

### 2. Test Event Flow

```javascript
// Test emit and receive
window.Homa.on('test', (data) => {
    console.log('Received:', data);
});

window.Homa.emit('test', { message: 'Hello!' });
// Should log: "Received: { message: 'Hello!' }"
```

### 3. Test Form Change Detection

1. Open the site
2. Find a form field
3. Type something
4. Check console for: `[Homa Sync] Field "..." changed to: ...`

### 4. Test AI Command Execution

```javascript
// Simulate an AI command
window.Homa.emit('ai:command', {
    action_type: 'ui_interaction',
    command: 'HIGHLIGHT',
    target_selector: '.et_pb_button'
});
// The button should highlight
```

### 5. Test State Synchronization

```javascript
// Update state
window.Homa.updateState({ testValue: 123 });

// Get state
console.log(window.Homa.getState());
// Should include testValue: 123
```

### 6. Check Event History

```javascript
// View recent events
window.Homa.getEventHistory(10);
// Returns array of last 10 events
```

## Performance Considerations

### Debouncing

All form field listeners are debounced by 300ms to prevent excessive event firing while user types.

### Memory Management

All event listeners return cleanup functions:

```javascript
const cleanup = window.Homa.on('event', callback);
// When done:
cleanup();
```

React hooks automatically cleanup on unmount.

### Event History Limit

Event history is limited to 100 events to prevent memory leaks.

## Security Considerations

1. **No Sensitive Data in Events**: Never emit passwords or tokens
2. **Nonce Verification**: API calls still use WordPress nonces
3. **Input Sanitization**: All user input is sanitized before use
4. **CORS Protection**: Events are scoped to window, not cross-origin

## Breaking Changes

None. This PR is additive and maintains backward compatibility with existing code.

## Migration Guide

### For Custom Scripts

If you have custom scripts that need to integrate:

```javascript
// Old way (still works)
document.addEventListener('homa_site_updated', handler);

// New way (recommended)
window.Homa.on('site:input_change', handler);
```

### For React Components

```jsx
// Old way
useEffect(() => {
    window.addEventListener('homa_site_updated', handler);
    return () => window.removeEventListener('homa_site_updated', handler);
}, []);

// New way
import { useHomaEvent } from '../homaReactBridge';
useHomaEvent('site:input_change', handler);
```

## Troubleshooting

### Event Bus Not Available

If `window.Homa` is undefined:
1. Check browser console for script loading errors
2. Ensure scripts are loading in correct order
3. Event bus loads at priority 5, before all other Homa scripts

### Events Not Firing

1. Check connectivity: `window.Homa.checkConnectivity()`
2. Check event history: `window.Homa.getEventHistory()`
3. Verify listener is registered: `window.HomaDebug.EventBus.getListeners()`

### React Not Receiving Events

1. Ensure React bundle is rebuilt: `npm run build`
2. Check React is ready: Look for "react:ready" event in history
3. Verify hooks are imported correctly

## Future Enhancements (PR 7)

- Long-term memory integration
- Persistent context storage
- CRM sync via Event Bus
- Analytics event tracking
- Performance metrics

## Files Added

- `assets/js/homa-event-bus.js` (256 lines)
- `assets/js/homa-command-interpreter.js` (425 lines)
- `assets/react/homaReactBridge.js` (195 lines)

## Files Modified

- `assets/js/homa-indexer.js` - Added event bus integration
- `assets/js/homa-orchestrator.js` - Added state sync
- `assets/react/components/HomaSidebar.jsx` - Added event bus hooks
- `includes/HT_Parallel_UI.php` - Updated script loading
- `assets/build/homa-sidebar.js` - Rebuilt bundle

## Total Changes

- +876 lines added
- -45 lines removed
- 8 files changed
