<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8" />
    <title>Logovi navika - Habit Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/dashboard.css" rel="stylesheet" />
    <style>
        .export-buttons button,
        .export-buttons a {
            min-width: 120px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="../pages/dashboard.php" class="btn btn-outline-secondary mb-3">&larr; Nazad na Dashboard</a>

    <h4 class="mb-4">Logovi navika</h4>

    <form id="filterForm" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-sm-12 col-md-3">
                <input type="text" id="user" name="user" class="form-control" placeholder="Korisnik" aria-label="Korisnik">
            </div>
            <div class="col-sm-6 col-md-3">
                <input type="date" id="date_from" name="date_from" class="form-control" aria-label="Od datuma">
            </div>
            <div class="col-sm-6 col-md-3">
                <input type="date" id="date_to" name="date_to" class="form-control" aria-label="Do datuma">
            </div>
            <div class="col-sm-12 col-md-3 d-grid gap-2">
                <button type="submit" class="btn btn-primary">Filtriraj</button>
                <div class="export-buttons d-flex gap-2 mt-2 justify-content-center justify-content-md-start">
                    <a href="#" id="exportJson" class="btn btn-outline-info btn-sm" target="_blank" rel="noopener">Export JSON</a>
                    <a href="#" id="exportXml" class="btn btn-outline-warning btn-sm" target="_blank" rel="noopener">Export XML</a>
                </div>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Korisnik</th>
                    <th>Navika</th>
                    <th>Datum</th>
                </tr>
            </thead>
            <tbody id="logsTableBody">
                <!-- Podaci će se učitavati AJAX-om -->
            </tbody>
        </table>
    </div>

    <div class="d-grid">
        <button id="loadMoreBtn" class="btn btn-primary mt-3">Učitaj još</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    let offset = 0;
    const limit = 20;
    let loading = false;
    let allLoaded = false;

    function loadLogs(reset = false) {
        if (loading || allLoaded) return;
        loading = true;
        $('#loadMoreBtn').prop('disabled', true).text('Učitavanje...');

        if(reset) {
            offset = 0;
            allLoaded = false;
            $('#logsTableBody').empty();
        }

        const user = $('#user').val();
        const date_from = $('#date_from').val();
        const date_to = $('#date_to').val();

        $.ajax({
            url: '../scripts/logs_fetch.php',
            method: 'GET',
            data: {
                user: user,
                date_from: date_from,
                date_to: date_to,
                limit: limit,
                offset: offset
            },
            dataType: 'json',
            success: function(data) {
                if (reset && data.length === 0) {
                    $('#logsTableBody').html('<tr><td colspan="3" class="text-center text-muted">Nema zapisa za zadate filtere.</td></tr>');
                    allLoaded = true;
                } else {
                    if(data.length < limit) {
                        allLoaded = true;
                        $('#loadMoreBtn').hide();
                    } else {
                        $('#loadMoreBtn').show();
                    }
                    data.forEach(log => {
                        $('#logsTableBody').append(`
                            <tr>
                                <td>${escapeHtml(log.username)}</td>
                                <td>${escapeHtml(log.habit_name)}</td>
                                <td>${escapeHtml(log.log_date)}</td>
                            </tr>
                        `);
                    });
                    offset += data.length;
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Greška pri učitavanju podataka: ' + textStatus + ' - ' + errorThrown);
                console.error('Detalji greške:', jqXHR.responseText);
            },
            complete: function() {
                loading = false;
                if (!allLoaded) {
                    $('#loadMoreBtn').prop('disabled', false).text('Učitaj još');
                }
            }
        });
    }

    function escapeHtml(text) {
        return text
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
    }

    loadLogs(true);

    $('#filterForm').on('submit', function(e){
        e.preventDefault();
        loadLogs(true);
    });

    function getExportUrl(format){
        const params = new URLSearchParams({
            user: $('#user').val(),
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            export: format
        });
        return '../pages/logs.php?' + params.toString();
    }

    $('#exportJson').attr('href', getExportUrl('json'));
    $('#exportXml').attr('href', getExportUrl('xml'));

    $('#filterForm input').on('input change', function(){
        $('#exportJson').attr('href', getExportUrl('json'));
        $('#exportXml').attr('href', getExportUrl('xml'));
    });

    $('#loadMoreBtn').on('click', function(){
        loadLogs();
    });
});
</script>
</body>
</html>
