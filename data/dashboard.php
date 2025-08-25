<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Nếu chưa đăng nhập, chuyển hướng đến trang index.html
    header('Location: index.html');
    exit;
}

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <title>Update Data</title>
  <style>
    /* Đặt body sử dụng Flexbox để căn giữa nội dung */
    body {
        display: flex;
        justify-content: center; /* Căn giữa ngang */
        align-items: center;    /* Căn giữa dọc */
        height: 100vh;          /* Chiều cao toàn màn hình */
        margin: 0;              /* Loại bỏ margin mặc định */
        background-color: #f2f2f2; /* Màu nền */
        font-family: Arial, sans-serif; /* Font chữ */
    }
    .dashboard-container {
        width: 90%;
        max-width: 300px; /* Giới hạn độ rộng tối đa */
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #fff; /* Màu nền trắng */
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        position: relative; /* Để position absolute của suggestion-list hoạt động chính xác */
    }
    .header {
        background-color: #007bff;
        color: white;
        padding: 20px;
        text-align: center;
        border-radius: 5px 5px 0 0;
    }
    .header h3 {
        margin: 0;
        font-size: 1.2em; /* Giảm kích thước chữ */
    }
    .logout-button {
        display: block;
        margin: 20px auto; /* Căn giữa ngang */
        padding: 8px 16px; /* Giảm padding */
        background-color: #dc3545;
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        cursor: pointer;
        width: 150px; /* Giảm độ rộng */
        box-sizing: border-box; /* Đảm bảo padding và border được tính vào width */
        font-size: 14px; /* Giảm kích thước chữ */
        transition: background 0.3s;
    }
    .logout-button:hover {
        background-color: #c82333;
    }
    h1 {
        text-align: center;
        margin-top: 20px;
        color: #333;
        font-size: 1.5em; /* Điều chỉnh kích thước chữ */
    }
    .search-container {
        position: relative;
        width: 100%;
    }
    #searchInput {
        width: 100%;
        padding: 10px;
        margin-top: 20px;
        box-sizing: border-box;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .suggestion-list {
        border: 1px solid #ddd;
        max-height: 150px;
        overflow-y: auto;
        background: #fff;
        position: absolute;
        width: 100%;
        top: 100%;
        left: 0;
        z-index: 1000;
        border-radius: 0 0 5px 5px;
        box-sizing: border-box;
        display: none;
    }
    .suggestion-list div {
        padding: 10px;
        cursor: pointer;
        text-align: center;
    }
    .suggestion-list div:hover {
        background: #f0f0f0;
    }
    .form-container {
        display: none;
        margin-top: 20px;
        border: 1px solid #ddd;
        padding: 20px;
        background: #fff;
        border-radius: 5px;
        position: relative;
    }
    .form-container h3 {
        text-align: center;
        color: #007bff;
        font-size: 1.1em;
    }
    .form-container label {
        display: block;
        margin-top: 10px;
        color: #333;
    }
    .form-container input {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        box-sizing: border-box;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .form-container button {
        margin-top: 15px;
        padding: 10px;
        width: 100%;
        background-color: #28a745;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        transition: background 0.3s;
        font-size: 14px;
    }
    .form-container button:hover {
        background-color: #218838;
    }
    .update-message {
        display: none;
        margin-top: 10px;
        padding: 10px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 5px;
        text-align: center;
    }

    @media (max-width: 320px) {
        .dashboard-container {
            padding: 15px;
        }
        .header {
            padding: 15px;
        }
        .header h3 {
            font-size: 1em;
        }
        .logout-button {
            padding: 6px 12px;
            font-size: 12px;
            width: 120px;
        }
        .form-container {
            padding: 15px;
        }
        .form-container h3 {
            font-size: 1em;
        }
        .form-container button {
            padding: 8px;
            font-size: 12px;
        }
        #searchInput {
            padding: 8px;
        }
        .suggestion-list div {
            padding: 8px;
            font-size: 14px;
        }
        h1 {
            font-size: 1.2em;
        }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- Header của Dashboard -->
    <div class="header">
      <h3>Dashboard Update Suburb</h3>
    </div>

    <!-- Nút đăng xuất -->
    <a id="logoutButton" class="logout-button">Log out</a>

    <!-- Tiêu đề chính -->
    <h1></h1>

    <!-- Container cho ô nhập liệu và danh sách gợi ý -->
    <div class="search-container">
      <!-- Tìm kiếm suburb -->
      <input type="text" id="searchInput" placeholder="Enter Suburb..." autocomplete="off" />
      <div id="suggestionList" class="suggestion-list"></div>
    </div>

    <!-- Hiển thị form chỉnh sửa khi chọn một Suburb -->
    <div id="editFormContainer" class="form-container">
      <h3>Update Data Form</h3>
      <form id="editForm">
        <input type="hidden" id="editIndex" name="index" />
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
        <label for="editSuburb">Suburb:</label>
        <input type="text" id="editSuburb" name="suburb" required disabled />

        <label for="editRun">Run:</label>
        <input type="number" id="editRun" name="run" required />

        <label for="editZone">Zone:</label>
        <input type="number" id="editZone" name="zone" required />

        <label for="editPostcode">Postcode:</label>
        <input type="number" id="editPostcode" name="postcode" required />

        <button type="submit">Update</button>
      </form>
    </div>

    <!-- Khu vực hiển thị thông báo cập nhật -->
    <div id="updateMessage" class="update-message">Data Updated</div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('searchInput');
      const suggestionList = document.getElementById('suggestionList');
      const editFormContainer = document.getElementById('editFormContainer');
      const editForm = document.getElementById('editForm');
      const logoutButton = document.getElementById('logoutButton');
      const updateMessage = document.getElementById('updateMessage');

      // Thêm debounce cho tìm kiếm
      function debounce(func, wait) {
        let timeout;
        return function(...args) {
          clearTimeout(timeout);
          timeout = setTimeout(() => func.apply(this, args), wait);
        };
      }

      function showUpdateMessage(message) {
        updateMessage.textContent = message;
        updateMessage.style.display = 'block';
        setTimeout(() => updateMessage.style.display = 'none', 3000);
      }

      searchInput.addEventListener('input', debounce(() => {
        const query = searchInput.value.trim();
        if (query.length < 1) {
          suggestionList.innerHTML = '';
          suggestionList.style.display = 'none';
          return;
        }

        fetch(`json-handler.php?action=search&query=${encodeURIComponent(query)}`)
          .then(response => response.json())
          .then(data => {
            suggestionList.innerHTML = '';
            if (data.length > 0) {
              data.forEach(suburb => {
                const div = document.createElement('div');
                div.textContent = suburb.Suburb;
                div.dataset.index = suburb.index; // Lưu trữ chỉ số thực
                div.dataset.run = suburb.Run;
                div.dataset.zone = suburb.Zone;
                div.dataset.postcode = suburb.Postcode;
                div.addEventListener('click', () => selectSuburb(suburb));
                suggestionList.appendChild(div);
              });
              suggestionList.style.display = 'block';
            } else {
              suggestionList.innerHTML = '<div>Không tìm thấy Suburb nào.</div>';
              suggestionList.style.display = 'block';
            }
          })
          .catch(error => console.error('Lỗi tìm kiếm:', error));
      }, 300));

      // Hàm xử lý khi chọn một suburb
      function selectSuburb(suburb) {
        searchInput.value = '';
        suggestionList.innerHTML = '';
        suggestionList.style.display = 'none';
        editFormContainer.style.display = 'block';

        // Điền thông tin suburb vào form và vô hiệu hóa ô nhập Suburb
        document.getElementById('editIndex').value = suburb.index;
        document.getElementById('editSuburb').value = suburb.Suburb;
        document.getElementById('editSuburb').disabled = true; // Vô hiệu hóa ô nhập Suburb
        document.getElementById('editRun').value = suburb.Run;
        document.getElementById('editZone').value = suburb.Zone;
        document.getElementById('editPostcode').value = suburb.Postcode;

        editFormContainer.scrollIntoView({ behavior: 'smooth' });
      }

      editForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const suburbIndex = document.getElementById('editIndex').value;
        if (!suburbIndex) {
          alert('Vui lòng chọn một Suburb từ danh sách gợi ý.');
          return;
        }

        const updatedSuburb = {
          Suburb: document.getElementById('editSuburb').value.trim(),
          Run: document.getElementById('editRun').value.trim(),
          Zone: document.getElementById('editZone').value.trim(),
          Postcode: document.getElementById('editPostcode').value.trim()
        };

        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        fetch(`json-handler.php?action=update&index=${encodeURIComponent(suburbIndex)}`, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify(updatedSuburb)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showUpdateMessage('Data Updated');
            editForm.reset();
            editFormContainer.style.display = 'none';
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          console.error('Lỗi cập nhật:', error);
          alert('Đã xảy ra lỗi khi cập nhật. Vui lòng thử lại.');
        });
      });

      logoutButton.addEventListener('click', () => {
        fetch('logout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.html';
            } else {
                alert('Đăng xuất không thành công.');
            }
        })
        .catch(error => {
            console.error('Lỗi khi đăng xuất:', error);
            alert('Đã xảy ra lỗi khi đăng xuất. Vui lòng thử lại.');
        });
      });
    });
  </script>
</body>
</html>
