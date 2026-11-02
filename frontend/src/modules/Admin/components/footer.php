<?php
/**
 * Footer Component cho Module Admin
 * Vị trí: frontend/src/modules/Admin/components/footer.php
 */
if (!isset($assetsUrl) || !isset($projectRootDir)) {
  $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
  $baseUrl = '';
  if (($pos = strpos($scriptName, '/backend/')) !== false) {
    $baseUrl = substr($scriptName, 0, $pos);
  } elseif (($pos = strpos($scriptName, '/frontend/')) !== false) {
    $baseUrl = substr($scriptName, 0, $pos);
  } else {
    $baseUrl = rtrim(dirname($scriptName), '/');
  }
  $assetsUrl = ($baseUrl !== '' ? $baseUrl : '') . '/frontend/public/assets';
  $projectRootDir = dirname(__DIR__, 5);
}
?>
</div>
<!-- END: MainContainerLayout -->
<!-- BEGIN: InteractiveScripts -->
<script
  src="<?= htmlspecialchars($assetsUrl, ENT_QUOTES, 'UTF-8') ?>/js/script.js?v=<?= @filemtime($projectRootDir . '/frontend/public/assets/js/script.js') ?: time() ?>"></script>
<!-- END: InteractiveScripts -->
</body>

</html>

