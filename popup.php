<?php
$config = require 'popup_config.php';
if (!($config['enabled'] ?? true)) return;

$welcomeText = $config['welcome_text'] ?? 'Willkommen zur Coverage Map!';
$subText = $config['welcome_subtext'] ?? '';
$discords = $config['discords'] ?? [];
$buttonText = $config['confirm_button_text'] ?? 'Weiter zur Karte'; // NEU
?>

<style>
#popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

#popup-content {
  background: white;
  padding: 30px;
  border-radius: 8px;
  text-align: center;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 0 15px rgba(0,0,0,0.3);
}

#popup-content h2 {
  margin-top: 0;
}

#popup-content a {
  display: block;
  margin: 10px 0;
  color: #0077cc;
  text-decoration: none;
}

#popup-content a:hover {
  text-decoration: underline;
}

#popup-confirm {
  margin-top: 20px;
  padding: 10px 20px;
  border: none;
  background: #0077cc;
  color: white;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
}

#popup-confirm:hover {
  background: #005fa3;
}
</style>

<div id="popup-overlay">
  <div id="popup-content">
    <h2><?= htmlspecialchars($welcomeText) ?></h2>
    <?php if ($subText): ?><p><?= $subText ?></p><?php endif; ?>
    <?php foreach ($discords as $name => $url): ?>
      <a href="<?= htmlspecialchars($url) ?>" target="_blank">🔗 <?= htmlspecialchars($name) ?></a>
    <?php endforeach; ?>
    <button id="popup-confirm"><?= htmlspecialchars($buttonText) ?></button>
  </div>
</div>