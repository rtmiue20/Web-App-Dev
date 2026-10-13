<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Thêm Danh Mục Mới</h4>
                </div>
                <div class="card-body">
                    <form id="addCategoryForm">
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Ví dụ: Cắm trại" required>
                        </div>
                        <div class="form-group">
                            <label for="description" class="font-weight-bold">Mô tả chi tiết</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Nhập mô tả cho danh mục..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Lưu Danh Mục</button>
                        <a href="/categories" class="btn btn-secondary">Hủy</a>
                    </form>
                    
                    <div id="alertMessage" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Sử dụng function() truyền thống, ĐẢM BẢO KHÔNG DÙNG LAMBDA
$(document).ready(function() {
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault(); 

        $.ajax({
            url: '/category/store', 
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json', 
            success: function(response) {
                if(response.status === 'success') {
                    $('#alertMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#addCategoryForm')[0].reset(); 
                } else {
                    $('#alertMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            }
        });
    });
});
</script>