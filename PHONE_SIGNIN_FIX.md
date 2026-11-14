# Phone Sign In Fix

Fixed the public header so the Sign In button is not hidden on tablet/phone.

Cause:
`.nav-user .signin-link { display:none; }` inside the <=1180px media query.

Fix:
- Sign In remains visible on screens <= 980px.
- On <=700px it is a compact button with icon + text.
- On <=520px it becomes a compact login icon to prevent header overflow.
- Get Started is also compact on small screens.
