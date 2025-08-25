document.addEventListener('DOMContentLoaded', () => {
  let data = [];

  // Tải dữ liệu từ file data.json
  fetch('data/data.json')
    .then(response => response.json())
    .then(json => {
      data = json;
      console.log('Data loaded:', data);
    })
    .catch(error => console.error('Error loading data:', error));

  const suburbInput = document.getElementById('suburb-input');
  const runInput = document.getElementById('run-input');
  const suggestions = document.getElementById('suggestions');
  const runSuggestions = document.getElementById('run-suggestions');
  const result = document.getElementById('result');
  const runInfo = document.getElementById('run-info');
  const suburbName = document.getElementById('suburb');
  const zone = document.getElementById('zone');
  const run = document.getElementById('run');
  const postcode = document.getElementById('postcode');
  const formattedResult = document.getElementById('formatted-result');
  const zoneFromRun = document.getElementById('zone-from-run');
  const runFromRun = document.getElementById('run-from-run');

  // Popup modal elements
  const popupOverlay = document.getElementById('popupOverlay');
  const modalTitle = document.getElementById('modal-title');
  const modalRunList = document.getElementById('modalRunList');
  const modalCloseButton = document.getElementById('modalCloseButton');

  // Đóng popup khi nhấn nút Close
  modalCloseButton.addEventListener('click', () => {
    popupOverlay.style.display = 'none';
  });

  runInfo.style.display = 'none';

  // Tab navigation
  const tabButtons = document.querySelectorAll('.tab-button');
  const tabContents = document.querySelectorAll('.tab-content');

  tabButtons.forEach(button => {
    button.addEventListener('click', () => {
      tabButtons.forEach(btn => btn.classList.remove('active'));
      tabContents.forEach(content => content.classList.remove('active'));

      button.classList.add('active');
      const tabId = button.getAttribute('data-tab');
      document.getElementById(tabId).classList.add('active');
    });
  });

  // Hàm loại bỏ trùng lặp theo Run Number
  function removeRunDuplicates(arr) {
    const seen = new Set();
    return arr.filter(item => {
      const val = item.Run.toString();
      if (seen.has(val)) return false;
      seen.add(val);
      return true;
    });
  }

  // Hàm hiển thị popup (Dùng chung cho cả hai tab)
  function showRunPopup(zoneNumber) {
    let runsInZone = data.filter(item => String(item.Zone) === String(zoneNumber));
    let uniqueRuns = removeRunDuplicates(runsInZone);
    uniqueRuns.sort((a, b) => a.Run - b.Run);

    modalTitle.textContent = `Run Numbers in Zone ${zoneNumber}`;
    let tableHTML = `<table class="run-table">
                        <thead><tr><th>Run Number</th></tr></thead>
                        <tbody>`;
    uniqueRuns.forEach(item => {
      tableHTML += `<tr><td>Run ${item.Run}</td></tr>`;
    });
    tableHTML += `</tbody></table>`;
    modalRunList.innerHTML = tableHTML;

    popupOverlay.style.display = 'flex';
  }

  // Xử lý tìm kiếm Suburb
  suburbInput.addEventListener('input', function() {
    const input = suburbInput.value.toLowerCase();
    suggestions.innerHTML = '';

    if (!input) {
      suggestions.style.display = 'none';
      result.style.display = 'none';
      return;
    }

    let matchedSuburbs = data.filter(item =>
      item.Suburb.toLowerCase().startsWith(input) ||
      (item.Postcode && item.Postcode.toString().startsWith(input))
    );

    matchedSuburbs.sort((a, b) => a.Suburb.localeCompare(b.Suburb));

    if (!matchedSuburbs.length) {
      suggestions.style.display = 'block';
      suggestions.innerHTML = `
        <div>No suburb matched. Note:
          <br>GOS postcode: 2250 to 2263
          <br>NTL postcode: 2264 to 2327
          <br>WOL postcode: 2500 to 2533
        </div>`;
    } else {
      suggestions.style.display = 'block';
      matchedSuburbs.forEach(suburbItem => {
        const suggestion = document.createElement('div');
        suggestion.textContent = `${suburbItem.Suburb} (${suburbItem.Postcode})`;
        suggestion.addEventListener('click', () => {
          suburbInput.value = '';
          suburbName.textContent = suburbItem.Suburb;
          zone.textContent = suburbItem.Zone;
          run.textContent = suburbItem.Run;
          postcode.textContent = suburbItem.Postcode;

          formattedResult.innerText = `(SZ${suburbItem.Zone}${suburbItem.Run.toString().padStart(3, '0')})`;

          let oldShowAll = document.getElementById('show-all-runs');
          if (oldShowAll) oldShowAll.remove();

          let showAllRuns = document.createElement('p');
          showAllRuns.id = 'show-all-runs';
          showAllRuns.classList.add('small-text', 'clickable');
          showAllRuns.style.cursor = 'pointer';
          showAllRuns.textContent = `Click to show all Run Numbers in Zone ${suburbItem.Zone}`;
          result.appendChild(showAllRuns);

          showAllRuns.addEventListener('click', () => {
            showRunPopup(suburbItem.Zone);
          });

          result.style.display = 'block';
          suggestions.innerHTML = '';
          suggestions.style.display = 'none';
        });
        suggestions.appendChild(suggestion);
      });
    }
  });

  // Xử lý tìm kiếm Run Number
  runInput.addEventListener('input', function() {
    const inputRun = runInput.value;
    runSuggestions.innerHTML = '';

    if (!inputRun) {
      runSuggestions.style.display = 'none';
      runInfo.style.display = 'none';
      return;
    }

    let matchedRuns = data.filter(item => item.Run.toString().startsWith(inputRun));
    matchedRuns = removeRunDuplicates(matchedRuns);
    matchedRuns.sort((a, b) => a.Run - b.Run);

    if (!matchedRuns.length) {
      runSuggestions.style.display = 'block';
      runSuggestions.innerHTML = `<div>No Run Number matched.</div>`;
    } else {
      runSuggestions.style.display = 'block';
      matchedRuns.forEach(runItem => {
        const runSuggestion = document.createElement('div');
        runSuggestion.textContent = `Run ${runItem.Run} (Zone ${runItem.Zone})`;
        runSuggestion.addEventListener('click', () => {
          runInput.value = '';
          runFromRun.textContent = runItem.Run;
          zoneFromRun.textContent = runItem.Zone;
          runInfo.style.display = 'block';

          let oldShowAll = document.getElementById('show-all-runs-run');
          if (oldShowAll) oldShowAll.remove();

          let showAllRuns = document.createElement('p');
          showAllRuns.id = 'show-all-runs-run';
          showAllRuns.classList.add('small-text', 'clickable');
          showAllRuns.style.cursor = 'pointer';
          showAllRuns.textContent = `Show all Run Numbers in Zone ${runItem.Zone}`;
          runInfo.appendChild(showAllRuns);

          showAllRuns.addEventListener('click', () => {
            showRunPopup(runItem.Zone);
          });

          runSuggestions.innerHTML = '';
          runSuggestions.style.display = 'none';
        });
        runSuggestions.appendChild(runSuggestion);
      });
    }
  });
});
