# Project workflow

- Do not run unit tests, feature tests, full test suites, build commands, or other automated verification by default.
- The user performs manual testing. Run automated checks only when the user explicitly asks for a named check or otherwise authorizes it.
- When handing off a change, state the relevant routes or UI areas for the user to test manually.
