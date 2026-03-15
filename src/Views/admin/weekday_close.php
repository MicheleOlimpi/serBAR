<h4>GIORNI CHIUSURA</h4>

<?php if (!empty($saved)): ?>
  <div class="alert alert-success">Configurazione salvata.</div>
<?php endif; ?>

<form method="post">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>Giorno settimana</th>
        <th>Tipo giorno</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?= htmlspecialchars((string) $row['weekday_name']) ?></td>
          <td>
            <select class="form-select" name="weekday[<?= (int) $row['weekday_number'] ?>]" required>
              <?php foreach ($dayTypes as $dayType): ?>
                <option value="<?= (int) $dayType['id'] ?>" <?= (int) $row['day_type_id'] === (int) $dayType['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) $dayType['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <button class="btn btn-success" type="submit">Salva</button>
</form>
