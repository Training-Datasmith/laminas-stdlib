# Architecture: laminas-stdlib

## Purpose
A collection of general-purpose PHP utility classes and data structures used throughout the Laminas framework. Provides priority queues, array utilities, string wrappers, options objects, and guard traits.

## Directory Structure
```
src/
  # Data structures:
  Fast_Priority_Queue.php       # SplPriorityQueue with stable insertion order and O(1) peek
  Priority_Queue.php            # Priority-ordered queue with serialization support
  Priority_List.php             # Doubly-linked priority list
  Spl_Priority_Queue.php        # Serializable SplPriorityQueue wrapper
  Spl_Queue.php / Spl_Stack.php # Serializable SplQueue/SplStack wrappers
  Array_Object.php              # Serializable ArrayObject
  Array_Stack.php               # Array-backed stack

  # Utilities:
  Array_Utils.php               # merge (deep), filter, iterate, type-check helpers
  String_Utils.php              # Encoding-aware string operations
  Glob.php                      # Cross-platform glob() with `**` support
  Error_Handler.php             # Converts PHP errors to exceptions within a scope
  Console_Helper.php            # Terminal width detection

  # Configuration:
  Abstract_Options.php          # Base class for typed config objects (setter/getter magic)
  Parameters.php                # Wrapper for HTTP parameters (query/post superglobals)

  # Interfaces:
  Array_Serializable_Interface.php
  Dispatchable_Interface.php
  Initializable_Interface.php
  Message_Interface.php / Request_Interface.php / Response_Interface.php

  # StringWrapper (encoding-aware string operations):
  StringWrapper/
    String_Wrapper_Interface.php
    Mb_String.php / Iconv.php / Intl.php / Native.php

  # Guard traits (pre-condition assertions):
  Guard/
    Null_Guard_Trait.php
    Empty_Guard_Trait.php
    Array_Or_Traversable_Guard_Trait.php
    All_Guards_Trait.php

  Exception/                    # Typed exceptions
```

## Key Design Decisions
- **Stable priority queues** — `Fast_Priority_Queue` and `Priority_Queue` extend PHP's `SplPriorityQueue` to guarantee stable insertion order for equal-priority items, which PHP's built-in queue does not guarantee.
- **`Abstract_Options`** — typed configuration objects with `__set`/`__get` magic that validate property names. Used across laminas components for IDE-discoverable configuration.
- **Guard traits** — lightweight pre-condition assertion traits that throw typed exceptions instead of bare `InvalidArgumentException`, improving error messages.
- **Encoding-aware strings** — `StringWrapper` classes handle multi-byte string operations (strlen, substr, strpos) in a unified way regardless of whether `mbstring`, `iconv`, or `intl` is available.

## Extension Points
- Extend `Abstract_Options` to create typed configuration value objects for any component.
- Use the guard traits as mix-ins in any class that needs pre-condition validation.
- Register a custom `StringWrapper` implementation via `StringUtils`.

## Dependency Flow
```
laminas components
  └─ laminas-stdlib (utility layer, no upward dependencies)
       ├─ Fast_Priority_Queue → EventManager, ServiceManager
       ├─ Abstract_Options → Session_Config, Listener_Options, etc.
       ├─ Array_Utils → Config merging (laminas-modulemanager)
       └─ Guard traits → pre-condition checks in any component
```
