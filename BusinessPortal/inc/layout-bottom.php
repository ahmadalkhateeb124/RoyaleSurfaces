        </main>

        <footer class="admin-foot">
            <span>&copy; <?= date('Y') ?> <?= e(site_name()) ?></span>
            <span>Signed in as <?= e(auth_user()['username']) ?></span>
        </footer>
    </div>

    <script src="<?= e(asset('BusinessPortal/assets/admin.js')) ?>" defer></script>
</body>

</html>
