@php
    // Default to null if $requiredRole is not provided
    $requiredRole = $requiredRole ?? null;
@endphp

<script>
(function() {
    // Hide body until session is validated
    document.body.style.visibility = 'hidden';

    async function validateSession(requiredRole) {
        const token = localStorage.getItem('auth_token');
        const baseUrl = window.location.origin;

        // If no token, redirect to home
        if (!token) {
            window.location.replace('{{ route("home") }}');
            return;
        }

        try {
            const res = await fetch(`${baseUrl}/api/validate-session`, {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const result = await res.json();

            // If session is invalid, clear token and redirect to home
            if (!res.ok || !result.user) {
                localStorage.removeItem('auth_token');
                window.location.replace('{{ route("home") }}');
                return;
            }

            const userRole = result.user.role;

            // If requiredRole is specified, check for exact match
            if (requiredRole) {
                if (userRole === requiredRole) {
                    // Role matches, show the page
                    document.body.style.visibility = 'visible';
                } else {
                    // Role doesn't match, redirect to home
                    window.location.replace('{{ route("home") }}');
                }
            } else {
                // No specific role required, redirect to home
                window.location.replace('{{ route("home") }}');
            }

        } catch (e) {
            localStorage.removeItem('auth_token');
            window.location.replace('{{ route("home") }}');
        }
    }
    // Call validation with the required role
    validateSession('{{ $requiredRole }}');
})();
</script>
