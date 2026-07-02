<div style="position: fixed; top: 16px; left: 16px; z-index: 99999;">
    <button id="sidebarToggleBtn"
            style="
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        ">

        <!-- Ícone hamburguer -->
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M3 12h18M3 18h18"/>
        </svg>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('sidebarToggleBtn');

        btn.addEventListener('click', () => {
            document.documentElement.classList.toggle('sidebar-collapsed');
        });
    });
</script>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/components/sidebar-toggle.blade.php ENDPATH**/ ?>