<?php
/**
 * Modal Chia sẻ Mã QR (QuickChart / GoQR REST API)
 * Cho phép quét mã QR bằng điện thoại để mở nhanh Chuyến đi dã ngoại (/trips/{id}),
 * Bài viết (/posts/{id}) hoặc Checklist (/checklists).
 */
?>
<div class="modal fade" id="qrShareModal" tabindex="-1" aria-labelledby="qrShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; background: #f7f8f4; color: #23261f; overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 pt-4 pb-3" style="background: #ffffff; border-color: rgba(35, 38, 31, 0.08) !important;">
                <div class="pe-2">
                    <span class="badge rounded-pill px-2.5 py-1 mb-1.5 d-inline-flex align-items-center gap-1" id="qrModalTypeBadge"
                          style="background: rgba(47, 94, 46, 0.12); color: #2f5e2e; border: 1px solid rgba(47, 94, 46, 0.25); font-size: 11px; font-weight: 600;">
                        <i class="bi bi-qr-code-scan"></i>
                        <span id="qrModalTypeText">Chia sẻ nhanh qua điện thoại</span>
                    </span>
                    <div class="fw-bold fs-5 mb-0" id="qrShareModalLabel" style="color: #1e241b !important; line-height: 1.3;">
                        Mã QR chia sẻ chuyến đi
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 py-3.5 text-center">
                <!-- Tên chuyến đi / bài viết -->
                <div id="qrModalItemTitle" class="fw-semibold mb-2.5 px-2 text-truncate" style="color: #2f5e2e; font-size: 14px;">
                    Outdoor Forum
                </div>

                <!-- Chuyển đổi chế độ URL khi chạy localhost (Localhost <-> LAN Wi-Fi IP) -->
                <div id="qrModalModeSwitch" class="d-none mb-3">
                    <div class="d-inline-flex p-1 rounded-pill border" style="background: #eef0ea; border-color: rgba(35, 38, 31, 0.12) !important;">
                        <button type="button" id="btnQrModeLan"
                                class="btn btn-sm rounded-pill px-3 py-1 fw-semibold"
                                style="font-size: 11.5px; background: #2f5e2e; color: #ffffff;"
                                onclick="ForumAjax.switchQrUrlMode('lan')"
                                title="Dùng IP mạng Wi-Fi nội bộ để điện thoại cùng Wi-Fi quét mở trực tiếp">
                            <i class="bi bi-wifi me-1"></i>IP Wi-Fi (Điện thoại)
                        </button>
                        <button type="button" id="btnQrModeStandard"
                                class="btn btn-sm rounded-pill px-3 py-1 fw-semibold"
                                style="font-size: 11.5px; background: transparent; color: #3a4235;"
                                onclick="ForumAjax.switchQrUrlMode('standard')">
                            <i class="bi bi-laptop me-1"></i>Localhost
                        </button>
                    </div>
                </div>

                <!-- Thông báo hướng dẫn kết nối cùng mạng Wi-Fi (Chỉ hiện khi chạy Localhost) -->
                <div id="qrModalWifiNotice" class="alert py-2 px-3 mb-3 text-start d-flex align-items-center gap-2 border shadow-none d-none"
                     style="font-size: 11.5px; border-radius: 14px; background: rgba(47, 94, 46, 0.08); border-color: rgba(47, 94, 46, 0.22) !important; color: #1e3a1d;">
                    <i class="bi bi-router text-success fs-6 flex-shrink-0"></i>
                    <div>
                        <strong>Yêu cầu Wi-Fi (Localhost):</strong> Điện thoại cần kết nối <strong>cùng mạng Wi-Fi</strong> với máy tính để quét mở link IP <code id="qrModalNoticeIp" class="px-1 py-0.5 rounded bg-white text-dark fw-bold">192.168.1.4:8000</code>.
                    </div>
                </div>

                <!-- Thông báo trực tuyến (Hiển thị mặc định trên môi trường Web / Hosting thực tế) -->
                <div id="qrModalPublicNotice" class="alert py-2 px-3 mb-3 text-start d-flex align-items-center gap-2 border shadow-none"
                     style="font-size: 11.5px; border-radius: 14px; background: rgba(47, 94, 46, 0.08); border-color: rgba(47, 94, 46, 0.22) !important; color: #1e3a1d;">
                    <i class="bi bi-globe2 text-success fs-6 flex-shrink-0"></i>
                    <div>
                        <strong>Mã QR trực tuyến:</strong> Bất kỳ ai quét bằng <strong>Camera hoặc Zalo (4G, 5G, Wi-Fi)</strong> đều mở xem được ngay tức thì!
                    </div>
                </div>

                <!-- Khung chứa ảnh QR Code -->
                <div class="position-relative d-inline-flex align-items-center justify-content-center p-3 rounded-4 bg-white shadow-sm mb-2.5"
                     style="width: 248px; height: 248px; border: 2px solid rgba(47, 94, 46, 0.22);">
                    <!-- Góc định vị Scanner UX -->
                    <span style="position: absolute; top: 8px; left: 8px; width: 18px; height: 18px; border-top: 3px solid #2f5e2e; border-left: 3px solid #2f5e2e; border-top-left-radius: 6px;"></span>
                    <span style="position: absolute; top: 8px; right: 8px; width: 18px; height: 18px; border-top: 3px solid #2f5e2e; border-right: 3px solid #2f5e2e; border-top-right-radius: 6px;"></span>
                    <span style="position: absolute; bottom: 8px; left: 8px; width: 18px; height: 18px; border-bottom: 3px solid #2f5e2e; border-left: 3px solid #2f5e2e; border-bottom-left-radius: 6px;"></span>
                    <span style="position: absolute; bottom: 8px; right: 8px; width: 18px; height: 18px; border-bottom: 3px solid #2f5e2e; border-right: 3px solid #2f5e2e; border-bottom-right-radius: 6px;"></span>

                    <!-- Spinner khi đang gọi API (Định vị tuyệt đối ở giữa khung, tự ẩn khi ảnh QR tải xong) -->
                    <div id="qrModalLoading" style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 2; background: #ffffff; border-radius: 14px;">
                        <div class="spinner-border text-success mb-2" role="status" style="width: 2rem; height: 2rem;"></div>
                        <span class="small text-muted" style="font-size: 12px;">Đang sinh mã QR từ API...</span>
                    </div>

                    <!-- Ảnh QR Code sinh từ API QuickChart -->
                    <img id="qrModalImage" src="" alt="Mã QR chia sẻ" class="img-fluid rounded-2"
                         style="width: 212px; height: 212px; object-fit: contain; display: block; position: relative; z-index: 1;">
                </div>

                <p class="small text-muted mb-2.5 px-2" style="font-size: 12.5px; line-height: 1.45;">
                    <i class="bi bi-phone me-1 text-success"></i>Mở <strong>Camera điện thoại</strong> hoặc <strong>Zalo</strong> quét mã để mở ngay trên di động.
                </p>

                <!-- Ô liên kết & Nút Sao chép nhanh -->
                <div class="input-group input-group-sm rounded-pill overflow-hidden border shadow-sm mb-2" style="background: #ffffff; border-color: rgba(35, 38, 31, 0.16) !important;">
                    <span class="input-group-text bg-white border-0 ps-3 text-muted">
                        <i class="bi bi-link-45deg fs-6"></i>
                    </span>
                    <input type="text" id="qrModalShareUrlInput" class="form-control border-0 bg-white shadow-none small"
                           style="font-size: 12px; color: #23261f;" readonly value="">
                    <button type="button" class="btn px-3 fw-semibold" id="btnCopyQrUrl"
                            style="background: #2f5e2e; color: #ffffff; font-size: 12px;"
                            onclick="ForumAjax.copyQrShareUrl()">
                        <i class="bi bi-clipboard-check me-1"></i>Sao chép
                    </button>
                </div>

                <!-- Tùy chỉnh URL nếu dùng mạng ngoài Wi-Fi qua Tunnel (Chỉ hiển thị khi chạy Localhost) -->
                <div id="qrCustomUrlCollapseWrapper" class="text-start mt-2 d-none">
                    <a class="small text-decoration-none d-inline-flex align-items-center gap-1" data-bs-toggle="collapse" href="#qrCustomUrlCollapse" role="button" aria-expanded="false" style="font-size: 11.5px; color: #4b663b;">
                        <i class="bi bi-globe2"></i>Tùy chỉnh URL (Ngrok / Cloudflare Tunnel) <i class="bi bi-chevron-down" style="font-size: 9px;"></i>
                    </a>
                    <div class="collapse mt-2" id="qrCustomUrlCollapse">
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border shadow-none" style="background: #ffffff; border-color: rgba(35, 38, 31, 0.18) !important;">
                            <input type="text" id="qrCustomUrlInput" class="form-control border-0 px-3 small" placeholder="https://abc.ngrok-free.app/posts/2" style="font-size: 11.5px;">
                            <button type="button" class="btn btn-dark px-3 fw-semibold" style="font-size: 11.5px;" onclick="ForumAjax.applyCustomQrUrl()">
                                Sinh lại QR
                            </button>
                        </div>
                        <div class="text-muted small mt-1 px-1" style="font-size: 11px;">
                            Dán đường link public khi lập trình localhost để người ngoài mạng có thể truy cập.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center"
                 style="background: #ffffff; border-color: rgba(35, 38, 31, 0.08) !important;">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-medium" data-bs-dismiss="modal">
                    Đóng
                </button>
                <a href="#" id="btnDownloadQrImage" target="_blank" rel="noopener" download="outdoor-forum-qr.png"
                   class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                   style="background: #9bbd55; color: #1a2414; border: 1px solid #8ab042;">
                    <i class="bi bi-download"></i> Tải mã QR
                </a>
            </div>
        </div>
    </div>
</div>

