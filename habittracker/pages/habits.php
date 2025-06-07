<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

if ($is_admin) {
    $stmt = $pdo->query("SELECT habits.*, users.username FROM habits JOIN users ON habits.user_id = users.id ORDER BY habits.created_at DESC");
    $habits = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $habits = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8" />
    <title>Moje Navike - Habit Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/dashboard.css" rel="stylesheet" />
    <style>
        /* Forma za dodavanje */
        #addHabitForm {
            background: #fff;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgb(0 0 0 / 0.1);
            margin-bottom: 30px;
        }
        #addHabitForm input, #addHabitForm textarea {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.6rem 1rem;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        #addHabitForm input:focus, #addHabitForm textarea:focus {
            outline: none;
            border-color: #4c6ef5;
            box-shadow: 0 0 10px #4c6ef5aa;
        }
        #addHabitForm button {
            border-radius: 12px;
            font-weight: 700;
            padding: 0.7rem 2rem;
            font-size: 1.1rem;
            background-color: #4c6ef5;
            border: none;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 6px 15px #4c6ef533;
        }
        #addHabitForm button:hover:not(:disabled) {
            background-color: #3954c2;
        }

        /* Tabela */
        .table thead th {
            vertical-align: middle;
        }
        .habit-name {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .habit-desc {
            font-size: 0.95rem;
            color: #6c757d;
        }
        .habit-date {
            font-size: 0.9rem;
            color: #495057;
        }

        /* Ikonice dugmadi */
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 6px;
            color: #495057;
            transition: color 0.2s;
            font-size: 1rem;
        }
        .icon-btn:hover {
            color: #0d6efd;
        }
        .icon-btn.delete:hover {
            color: #dc3545;
        }
        .icon-btn svg {
            width: 20px;
            height: 20px;
            vertical-align: middle;
        }

        /* Dugme Učitaj još */
        #loadMoreHabits {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.6rem 1.8rem;
            font-size: 1rem;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <a href="../pages/dashboard.php" class="btn btn-outline-primary mb-4">&larr; Nazad na Dashboard</a>

    <h4 class="mb-3">Dodaj novu naviku</h4>
    <form id="addHabitForm" autocomplete="off" novalidate>
        <input
            type="text"
            name="name"
            id="habitName"
            placeholder="Naziv navike"
            required minlength="2"
            maxlength="100"
            aria-label="Naziv navike"
        />
        <textarea
            name="description"
            id="habitDesc"
            placeholder="Opis (opciono)"
            rows="3"
            maxlength="500"
            aria-label="Opis navike"
        ></textarea>
        <button type="submit">Dodaj naviku</button>
        <div id="addHabitError" class="text-danger mt-2"></div>
    </form>

    <h4 class="mb-3">Moje Navike</h4>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Naziv</th>
                    <th>Opis</th>
                    <th>Datum dodavanja</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody id="habitsTableBody">
                <?php foreach ($habits as $habit): ?>
                    <tr data-habit-id="<?= $habit['id'] ?>">
                        <td class="habit-name"><?= htmlspecialchars($habit['name']) ?></td>
                        <td class="habit-desc"><?= htmlspecialchars($habit['description']) ?></td>
                        <td class="habit-date"><?= htmlspecialchars($habit['created_at']) ?></td>
                        <td>
                            <button class="icon-btn edit-btn" title="Izmeni" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M12.146 0.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708L4.207 15.5H1v-3.207L12.146.146zM11.207 3L13 4.793 12.207 5.586 10.414 3.793 11.207 3z"/>
                                </svg>
                            </button>
                            <button class="icon-btn delete-btn" title="Obriši" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm2-3A1.5 1.5 0 0 0 6 4h4a1.5 1.5 0 0 0-1.5-1.5zM4.5 6v7A1.5 1.5 0 0 0 6 14.5h4a1.5 1.5 0 0 0 1.5-1.5V6h-7z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <button id="loadMoreHabits" class="btn btn-primary mt-3 d-none">Učitaj još</button>
</div>

<!-- Modal za izmenu navike -->
<div class="modal fade" id="editHabitModal" tabindex="-1" aria-labelledby="editHabitLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editHabitForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editHabitLabel">Izmeni naviku</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="habit_id" id="editHabitId" />
        <div class="mb-3">
          <label for="editHabitName" class="form-label">Naziv</label>
          <input type="text" class="form-control" id="editHabitName" name="name" required minlength="2" maxlength="100" />
        </div>
        <div class="mb-3">
          <label for="editHabitDesc" class="form-label">Opis</label>
          <textarea class="form-control" id="editHabitDesc" name="description" rows="3" maxlength="500"></textarea>
        </div>
        <div id="editHabitError" class="text-danger"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Sačuvaj</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal za potvrdu brisanja -->
<div class="modal fade" id="deleteHabitModal" tabindex="-1" aria-labelledby="deleteHabitLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteHabitLabel">Potvrda brisanja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
      </div>
      <div class="modal-body">
        Da li ste sigurni da želite da obrišete ovu naviku?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Obriši</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(function() {
    const $tableBody = $('#habitsTableBody');
    const $search = $('#searchHabits');
    const $loadMore = $('#loadMoreHabits');
    const $addHabitForm = $('#addHabitForm');
    const $habitName = $('#habitName');
    const $habitDesc = $('#habitDesc');
    const $addHabitError = $('#addHabitError');

    const limit = 20;
    let offset = 0;
    let allLoaded = false;
    let loading = false;

    let habitToDelete = null;

    function escapeHtml(text) {
        return text
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
    }

    function loadHabits(reset = false) {
        if (loading || allLoaded) return;
        loading = true;
        if (reset) {
            offset = 0;
            allLoaded = false;
            $tableBody.empty();
            console.log("Tabela očišćena, učitavanje podataka...");
        }
        $loadMore.prop('disabled', true).text('Učitavanje...');

        $.ajax({
            url: '../scripts/habits_fetch.php',
            method: 'GET',
            data: {
                search: $search.val(),
                limit: limit,
                offset: offset
            },
            dataType: 'json',
            success: function(data) {
                if (reset && data.length === 0) {
                    $tableBody.html('<tr><td colspan="4" class="text-center text-muted">Nema rezultata.</td></tr>');
                    allLoaded = true;
                    $loadMore.hide();
                } else {
                    if (data.length < limit) {
                        allLoaded = true;
                        $loadMore.hide();
                    } else {
                        $loadMore.show();
                    }
                    data.forEach(habit => {
                        const row = `
                            <tr data-habit-id="${habit.id}">
                                <td class="habit-name">${escapeHtml(habit.name)}</td>
                                <td class="habit-desc">${escapeHtml(habit.description)}</td>
                                <td>${habit.created_at}</td>
                                <td>
                                    <button class="icon-btn edit-btn" title="Izmeni" type="button">
                                      <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M12.146 0.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708L4.207 15.5H1v-3.207L12.146.146zM11.207 3L13 4.793 12.207 5.586 10.414 3.793 11.207 3z"/>
                                      </svg>
                                    </button>
                                    <button class="icon-btn delete-btn" title="Obriši" type="button">
                                      <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm2-3A1.5 1.5 0 0 0 6 4h4a1.5 1.5 0 0 0-1.5-1.5zM4.5 6v7A1.5 1.5 0 0 0 6 14.5h4a1.5 1.5 0 0 0 1.5-1.5V6h-7z"/>
                                      </svg>
                                    </button>
                                </td>
                            </tr>`;
                        $tableBody.append(row);
                    });
                    offset += data.length;
                }
            },
            error: function() {
                alert('Greška pri učitavanju podataka.');
            },
            complete: function() {
                loading = false;
                $loadMore.prop('disabled', false).text('Učitaj još');
                console.log("Učitavanje završeno");
            }
        });
    }

    $search.on('input', function() {
        loadHabits(true);
    });

    $loadMore.on('click', function() {
        loadHabits();
    });

    // Dodavanje nove navike
    $addHabitForm.on('submit', function(e) {
        e.preventDefault();
        $addHabitError.text('');
        const name = $habitName.val().trim();
        if (name.length < 2) {
            $addHabitError.text('Naziv mora imati najmanje 2 karaktera.');
            return;
        }
        const description = $habitDesc.val().trim();

        $.post('../scripts/add_habit.php', {name, description}, function(response) {
            if(response.success){
                $habitName.val('');
                $habitDesc.val('');
                loadHabits(true);
            } else {
                $addHabitError.text(response.message || 'Greška pri dodavanju navike.');
            }
        }, 'json').fail(() => {
            $addHabitError.text('Greška pri komunikaciji sa serverom.');
        });
    });

    // Inicijalno učitavanje
    loadHabits(true);

    // Modal za izmenu
    const editModal = new bootstrap.Modal(document.getElementById('editHabitModal'));
    const $editHabitForm = $('#editHabitForm');
    const $editHabitError = $('#editHabitError');

    $(document).on('click', '.edit-btn', function() {
        const $row = $(this).closest('tr');
        $('#editHabitId').val($row.data('habit-id'));
        $('#editHabitName').val($row.find('.habit-name').text());
        $('#editHabitDesc').val($row.find('.habit-desc').text());
        $editHabitError.text('');
        editModal.show();
    });

    $editHabitForm.on('submit', function(e) {
        e.preventDefault();
        $editHabitError.text('');
        const formData = $(this).serialize();

        $.post('../scripts/edit_habit.php', formData, function(response) {
            if(response.success){
                editModal.hide();
                loadHabits(true);
            } else {
                $editHabitError.text(response.message || 'Greška pri izmeni navike.');
            }
        }, 'json').fail(() => {
            $editHabitError.text('Greška pri komunikaciji sa serverom.');
        });
    });

    // Modal za brisanje
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteHabitModal'));
    const $confirmDeleteBtn = $('#confirmDeleteBtn');

    $(document).on('click', '.delete-btn', function() {
        habitToDelete = $(this).closest('tr').data('habit-id');
        deleteModal.show();
    });

    $confirmDeleteBtn.on('click', function() {
        if (!habitToDelete) return;

        $.post('../scripts/delete_habit.php', { habit_id: habitToDelete }, function(response) {
            if(response.success){
                deleteModal.hide();
                setTimeout(() => {
                    loadHabits(true);
                    $('a.btn-outline-primary').focus();
                }, 300);
            } else {
                alert(response.message || 'Greška pri brisanju navike.');
            }
        }, 'json').fail(() => {
            alert('Greška pri komunikaciji sa serverom.');
        });
    });
});
</script>

</body>
</html>
