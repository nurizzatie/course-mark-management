<h2>Student Dashboard</h2>

<p>Total Mark: <?= $total ?> / <?= $totalPossible ?></p>

<div style="background: #eee; width: 100%; height: 20px;">
  <div style="background: green; height: 20px; width: <?= ($totalPossible > 0 ? ($total / $totalPossible) * 100 : 0) ?>%;"></div>
</div>

<h3>Component Breakdown</h3>
<table border="1" cellpadding="5">
  <tr>
    <th>Component</th>
    <th>Mark</th>
    <th>Total</th>
  </tr>
  <?php foreach ($marks as $m): ?>
    <tr>
      <td><?= htmlspecialchars($m['component']) ?></td>
     <td><?= $m['obtained_mark'] ?></td>
<td><?= $m['max_mark'] ?></td>

    </tr>
  <?php endforeach; ?>
</table>
