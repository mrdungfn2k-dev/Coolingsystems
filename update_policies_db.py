import sqlite3
import os

policies = [
    {
        'slug': 'dieu-khoan-bao-mat',
        'title': 'Chính sách bảo mật thông tin',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Mục đích và phạm vi thu thập</h3>
<p>Website thu thập các thông tin cần thiết phục vụ việc tư vấn, đặt hàng, giao nhận, thanh toán, bảo hành, chăm sóc khách hàng và giải quyết yêu cầu của khách hàng. Thông tin có thể bao gồm họ tên, số điện thoại, email, địa chỉ nhận hàng, thông tin đơn hàng và các dữ liệu khách hàng chủ động cung cấp.</p>
<p>Doanh nghiệp không yêu cầu khách hàng cung cấp các thông tin không cần thiết cho mục đích giao dịch.</p>

<h3>2. Phạm vi sử dụng thông tin</h3>
<p>Thông tin được sử dụng để xác nhận và xử lý đơn hàng; liên hệ giao nhận; hỗ trợ kỹ thuật, bảo hành; giải quyết khiếu nại; cải thiện chất lượng dịch vụ; thực hiện nghĩa vụ kế toán, thuế và các nghĩa vụ pháp lý khác.</p>
<p>Thông tin không được sử dụng cho mục đích trái với phạm vi đã thông báo nếu chưa có sự đồng ý phù hợp của khách hàng, trừ trường hợp pháp luật có quy định khác.</p>

<h3>3. Thời gian lưu trữ</h3>
<p>Dữ liệu được lưu trữ trong thời gian cần thiết để thực hiện các mục đích nêu trên hoặc theo thời hạn mà pháp luật yêu cầu. Khách hàng có thể yêu cầu xem xét, cập nhật hoặc xóa dữ liệu trong phạm vi pháp luật cho phép.</p>

<h3>4. Bảo mật và chia sẻ dữ liệu</h3>
<p>Doanh nghiệp áp dụng các biện pháp quản lý và kỹ thuật hợp lý nhằm hạn chế truy cập, sử dụng, tiết lộ hoặc thay đổi trái phép dữ liệu. Dữ liệu chỉ được chia sẻ cho đơn vị vận chuyển, thanh toán, nhà cung cấp dịch vụ kỹ thuật hoặc cơ quan nhà nước có thẩm quyền khi cần thiết và phù hợp quy định.</p>
<p>Doanh nghiệp không mua bán thông tin cá nhân của khách hàng.</p>

<h3>5. Quyền của khách hàng</h3>
<p>Khách hàng có quyền yêu cầu được biết, truy cập, chỉnh sửa, hạn chế hoặc phản đối việc xử lý dữ liệu và thực hiện các quyền khác theo quy định pháp luật về bảo vệ dữ liệu cá nhân. Yêu cầu được tiếp nhận thông qua các kênh liên hệ được công bố trên website.</p>

<h3>6. Tiếp nhận sự cố</h3>
<p>Khi phát hiện hoặc nghi ngờ dữ liệu cá nhân bị sử dụng sai mục đích hoặc có sự cố bảo mật, khách hàng nên liên hệ ngay với doanh nghiệp qua thông tin liên hệ công bố tại website để được kiểm tra và xử lý.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    },
    {
        'slug': 'chinh-sach',
        'title': 'Chính sách bảo mật thông tin',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Mục đích và phạm vi thu thập</h3>
<p>Website thu thập các thông tin cần thiết phục vụ việc tư vấn, đặt hàng, giao nhận, thanh toán, bảo hành, chăm sóc khách hàng và giải quyết yêu cầu của khách hàng. Thông tin có thể bao gồm họ tên, số điện thoại, email, địa chỉ nhận hàng, thông tin đơn hàng và các dữ liệu khách hàng chủ động cung cấp.</p>
<p>Doanh nghiệp không yêu cầu khách hàng cung cấp các thông tin không cần thiết cho mục đích giao dịch.</p>

<h3>2. Phạm vi sử dụng thông tin</h3>
<p>Thông tin được sử dụng để xác nhận và xử lý đơn hàng; liên hệ giao nhận; hỗ trợ kỹ thuật, bảo hành; giải quyết khiếu nại; cải thiện chất lượng dịch vụ; thực hiện nghĩa vụ kế toán, thuế và các nghĩa vụ pháp lý khác.</p>
<p>Thông tin không được sử dụng cho mục đích trái với phạm vi đã thông báo nếu chưa có sự đồng ý phù hợp của khách hàng, trừ trường hợp pháp luật có quy định khác.</p>

<h3>3. Thời gian lưu trữ</h3>
<p>Dữ liệu được lưu trữ trong thời gian cần thiết để thực hiện các mục đích nêu trên hoặc theo thời hạn mà pháp luật yêu cầu. Khách hàng có thể yêu cầu xem xét, cập nhật hoặc xóa dữ liệu trong phạm vi pháp luật cho phép.</p>

<h3>4. Bảo mật và chia sẻ dữ liệu</h3>
<p>Doanh nghiệp áp dụng các biện pháp quản lý và kỹ thuật hợp lý nhằm hạn chế truy cập, sử dụng, tiết lộ hoặc thay đổi trái phép dữ liệu. Dữ liệu chỉ được chia sẻ cho đơn vị vận chuyển, thanh toán, nhà cung cấp dịch vụ kỹ thuật hoặc cơ quan nhà nước có thẩm quyền khi cần thiết và phù hợp quy định.</p>
<p>Doanh nghiệp không mua bán thông tin cá nhân của khách hàng.</p>

<h3>5. Quyền của khách hàng</h3>
<p>Khách hàng có quyền yêu cầu được biết, truy cập, chỉnh sửa, hạn chế hoặc phản đối việc xử lý dữ liệu và thực hiện các quyền khác theo quy định pháp luật về bảo vệ dữ liệu cá nhân. Yêu cầu được tiếp nhận thông qua các kênh liên hệ được công bố trên website.</p>

<h3>6. Tiếp nhận sự cố</h3>
<p>Khi phát hiện hoặc nghi ngờ dữ liệu cá nhân bị sử dụng sai mục đích hoặc có sự cố bảo mật, khách hàng nên liên hệ ngay với doanh nghiệp qua thông tin liên hệ công bố tại website để được kiểm tra và xử lý.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    },
    {
        'slug': 'quy-trinh-giai-quyet-khieu-nai',
        'title': 'Phương thức tiếp nhận và giải quyết khiếu nại',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Kênh tiếp nhận</h3>
<p>Khách hàng có thể gửi phản ánh, yêu cầu hoặc khiếu nại thông qua các kênh hỗ trợ được công bố tại website coolingsystems.vn. Khi gửi yêu cầu, khách hàng nên cung cấp họ tên, thông tin liên hệ, mã đơn hàng (nếu có), nội dung sự việc và tài liệu/hình ảnh liên quan.</p>

<h3>2. Quy trình xử lý</h3>
<ul>
<li><strong>Bước 1 - Tiếp nhận:</strong> Doanh nghiệp ghi nhận thông tin và kiểm tra tính đầy đủ của hồ sơ.</li>
<li><strong>Bước 2 - Xác minh:</strong> Đối chiếu đơn hàng, chứng từ, tình trạng sản phẩm và thông tin từ các bên liên quan.</li>
<li><strong>Bước 3 - Phản hồi:</strong> Thông báo phương án xử lý cho khách hàng qua kênh liên hệ phù hợp.</li>
<li><strong>Bước 4 - Giải quyết:</strong> Thực hiện phương án đã thống nhất như hỗ trợ kỹ thuật, đổi/trả, bảo hành, hoàn tiền hoặc biện pháp khác tùy trường hợp.</li>
<li><strong>Bước 5 - Hoàn tất:</strong> Ghi nhận kết quả xử lý và lưu hồ sơ theo quy định.</li>
</ul>

<h3>3. Nguyên tắc giải quyết</h3>
<p>Khiếu nại được xử lý trên cơ sở thiện chí, khách quan, căn cứ vào thông tin giao dịch, chính sách đã công bố và quy định pháp luật. Trường hợp phát sinh tranh chấp không thể giải quyết bằng thương lượng, các bên có quyền yêu cầu cơ quan có thẩm quyền giải quyết theo pháp luật Việt Nam.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    },
    {
        'slug': 'chinh-sach-gia',
        'title': 'Chính sách giá',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Nguyên tắc niêm yết</h3>
<p>Giá sản phẩm được công bố trên website hoặc được doanh nghiệp xác nhận trong quá trình tư vấn/đặt hàng. Giá áp dụng cho từng sản phẩm có thể thay đổi theo thời điểm, phiên bản, mã phụ tùng, dòng xe, số lượng đặt mua và chương trình bán hàng.</p>

<h3>2. Xác nhận giá</h3>
<p>Trước khi hoàn tất giao dịch, khách hàng cần kiểm tra giá sản phẩm, số lượng, phí vận chuyển và các chi phí phát sinh (nếu có). Trong trường hợp giá hiển thị có sai sót kỹ thuật hoặc thông tin sản phẩm chưa chính xác, doanh nghiệp sẽ liên hệ để xác nhận lại trước khi thực hiện đơn hàng.</p>

<h3>3. Thuế và chứng từ</h3>
<p>Việc thể hiện giá đã bao gồm hoặc chưa bao gồm thuế, phí được thực hiện theo thông tin công bố tại từng sản phẩm, báo giá hoặc chứng từ giao dịch. Hóa đơn/chứng từ được cung cấp theo quy định pháp luật và thông tin giao dịch thực tế.</p>

<h3>4. Chương trình ưu đãi</h3>
<p>Chiết khấu, giá đại lý, khuyến mại hoặc ưu đãi (nếu có) được áp dụng theo điều kiện của từng chương trình và không mặc nhiên cộng dồn nếu doanh nghiệp không có thông báo khác.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    },
    {
        'slug': 'chinh-sach-thanh-toan',
        'title': 'Chính sách về thanh toán',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Phương thức thanh toán</h3>
<p>Tùy từng đơn hàng và phương thức được website/doanh nghiệp hỗ trợ tại thời điểm giao dịch, khách hàng có thể thanh toán bằng chuyển khoản ngân hàng, thanh toán khi nhận hàng (COD) hoặc phương thức thanh toán hợp pháp khác được thông báo khi xác nhận đơn.</p>

<h3>2. Xác nhận thanh toán</h3>
<p>Khách hàng cần thanh toán đúng số tiền và nội dung theo hướng dẫn của doanh nghiệp. Đơn hàng có thể chỉ được xử lý hoặc xuất kho sau khi doanh nghiệp xác nhận điều kiện thanh toán tương ứng đã được đáp ứng.</p>

<h3>3. An toàn thanh toán</h3>
<p>Khách hàng không nên cung cấp mật khẩu, mã OTP hoặc thông tin xác thực ngân hàng cho người khác. Doanh nghiệp không yêu cầu khách hàng cung cấp mật khẩu tài khoản ngân hàng hoặc mã OTP để xác nhận đơn hàng.</p>

<h3>4. Sai lệch giao dịch</h3>
<p>Nếu phát sinh thanh toán nhầm, thiếu, thừa hoặc giao dịch chưa được ghi nhận, khách hàng cần cung cấp chứng từ giao dịch để doanh nghiệp kiểm tra và phối hợp xử lý.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    },
    {
        'slug': 'dieu-kien-han-che-cung-cap',
        'title': 'Điều kiện hoặc hạn chế trong việc cung cấp hàng hóa, dịch vụ',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Phạm vi cung cấp</h3>
<p>Website cung cấp thông tin và hỗ trợ giao dịch các sản phẩm/phụ tùng, thiết bị liên quan đến hệ thống điều hòa, làm mát và các nhóm phụ tùng ô tô được doanh nghiệp kinh doanh hợp pháp.</p>

<h3>2. Điều kiện đặt hàng</h3>
<p>Khách hàng có trách nhiệm cung cấp thông tin chính xác để doanh nghiệp xác định đúng sản phẩm, đặc biệt đối với phụ tùng phụ thuộc vào hãng xe, dòng xe, đời xe, phiên bản, mã phụ tùng hoặc thông số kỹ thuật.</p>
<p>Đơn hàng chỉ được coi là được chấp nhận sau khi doanh nghiệp xác nhận khả năng cung cấp, giá, số lượng, phương thức giao hàng và các điều kiện liên quan.</p>

<h3>3. Các trường hợp có thể hạn chế hoặc từ chối</h3>
<p>Doanh nghiệp có thể từ chối, tạm dừng hoặc điều chỉnh đơn hàng khi sản phẩm hết hàng; thông tin khách hàng không đầy đủ; không xác minh được giao dịch; có dấu hiệu gian lận; khu vực giao nhận nằm ngoài phạm vi phục vụ; xảy ra sự kiện bất khả kháng; hoặc việc cung cấp có nguy cơ vi phạm pháp luật.</p>
<p>Khả năng tương thích của phụ tùng cần được xác định theo thông số thực tế. Khách hàng nên cung cấp mã phụ tùng, thông tin xe hoặc hình ảnh sản phẩm cũ khi được yêu cầu.</p>

<h3>4. Trách nhiệm sử dụng</h3>
<p>Việc lắp đặt phụ tùng ô tô nên được thực hiện bởi người có chuyên môn phù hợp. Các hư hỏng phát sinh do lắp đặt sai kỹ thuật, sử dụng sai mục đích hoặc tự ý thay đổi sản phẩm được xem xét theo điều kiện bảo hành/đổi trả áp dụng.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    },
    {
        'slug': 'chinh-sach-doi-tra',
        'title': 'Chính sách giao hàng, đổi trả và hoàn tiền',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Giao hàng</h3>
<p>Doanh nghiệp hỗ trợ giao hàng theo phạm vi và phương thức được xác nhận với khách hàng. Thời gian giao hàng phụ thuộc địa chỉ nhận hàng, tình trạng tồn kho, đơn vị vận chuyển và các yếu tố khách quan. Phí vận chuyển (nếu có) được thông báo trước hoặc trong quá trình xác nhận đơn.</p>

<h3>2. Kiểm tra khi nhận hàng</h3>
<p>Khách hàng nên kiểm tra tình trạng bao bì, số lượng, mã sản phẩm và tình trạng bên ngoài khi nhận hàng. Nếu phát hiện giao thiếu, sai sản phẩm hoặc hư hỏng do vận chuyển, khách hàng cần lưu lại hình ảnh/video và liên hệ doanh nghiệp sớm để được hỗ trợ.</p>

<h3>3. Đổi trả</h3>
<p>Yêu cầu đổi/trả được xem xét căn cứ vào tình trạng sản phẩm, nguyên nhân, thời điểm thông báo, điều kiện bảo hành và chính sách áp dụng cho từng sản phẩm. Sản phẩm cần được giữ nguyên các bộ phận, phụ kiện và chứng từ liên quan trong phạm vi có thể.</p>
<p>Trường hợp giao sai hàng hoặc sản phẩm có lỗi thuộc trách nhiệm của doanh nghiệp/nhà sản xuất theo điều kiện áp dụng, doanh nghiệp sẽ phối hợp đổi sản phẩm, bảo hành hoặc có phương án phù hợp.</p>

<h3>4. Hoàn tiền</h3>
<p>Nếu giao dịch đủ điều kiện hoàn tiền, khoản hoàn được thực hiện theo phương thức phù hợp sau khi doanh nghiệp hoàn tất xác minh. Thời gian tiền về tài khoản có thể phụ thuộc ngân hàng, trung gian thanh toán hoặc phương thức thanh toán ban đầu.</p>

<h3>5. Trường hợp không thuộc phạm vi đổi trả thông thường</h3>
<p>Có thể bao gồm sản phẩm bị hư hỏng do sử dụng hoặc lắp đặt sai kỹ thuật; sản phẩm bị thay đổi kết cấu; thiếu căn cứ xác minh giao dịch; hoặc trường hợp khác đã được thông báo rõ cho khách hàng trước giao dịch và phù hợp pháp luật.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    },
    {
        'slug': 'hinh-thuc-ho-tro-truc-tuyen',
        'title': 'Hình thức hỗ trợ trực tuyến',
        'content': '''<div class="policy-page">
<p class="policy-meta"><strong>CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM</strong><br>Website áp dụng: https://coolingsystems.vn/ | Mã số thuế: 0111594888</p>

<h3>1. Nội dung hỗ trợ</h3>
<p>Doanh nghiệp cung cấp hỗ trợ trực tuyến nhằm tư vấn lựa chọn sản phẩm, xác định mã phụ tùng và khả năng tương thích, hướng dẫn đặt hàng, kiểm tra tình trạng đơn, hỗ trợ giao nhận, thanh toán, bảo hành, đổi trả và tiếp nhận phản ánh/khiếu nại.</p>

<h3>2. Kênh hỗ trợ</h3>
<p>Các kênh hỗ trợ chính thức là các số điện thoại, email, biểu mẫu liên hệ, chức năng nhắn tin hoặc kênh trực tuyến khác được công bố trên website https://coolingsystems.vn/. Khách hàng nên ưu tiên sử dụng thông tin liên hệ được công bố trực tiếp trên website để tránh giả mạo.</p>

<h3>3. Thông tin cần cung cấp</h3>
<p>Để được hỗ trợ nhanh chóng, khách hàng nên cung cấp họ tên, số điện thoại, mã đơn hàng (nếu có), mã sản phẩm, dòng xe/đời xe và hình ảnh hoặc video liên quan đến vấn đề cần hỗ trợ.</p>

<h3>4. Nguyên tắc hỗ trợ</h3>
<p>Doanh nghiệp tiếp nhận yêu cầu trong phạm vi hoạt động và phản hồi theo mức độ phức tạp của từng trường hợp. Với các vấn đề kỹ thuật về phụ tùng ô tô, doanh nghiệp có thể yêu cầu bổ sung thông tin hoặc kiểm tra thực tế trước khi đưa ra phương án xử lý.</p>

<p class="policy-note"><em>Tài liệu này được công bố và áp dụng đối với hoạt động cung cấp hàng hóa, dịch vụ trên website nêu trên. Doanh nghiệp có thể cập nhật nội dung để phù hợp với hoạt động thực tế và quy định pháp luật tại từng thời điểm.</em></p>
</div>'''
    }
]

db_paths = [
    'database/cooling.sqlite',
    '/var/lib/coolingsystems/cooling.db',
    '/opt/coolingsystems/cooling.db',
    '/opt/coolingsystems/database/cooling.sqlite',
    '/opt/cooling-php/cooling.db'
]

for db_path in db_paths:
    if os.path.exists(db_path):
        conn = sqlite3.connect(db_path)
        c = conn.cursor()
        tbl_check = c.execute("SELECT name FROM sqlite_master WHERE type='table' AND name='static_pages'").fetchone()
        if not tbl_check:
            print(f"Table static_pages does not exist in {db_path}. Skipping.")
            conn.close()
            continue
        for p in policies:
            c.execute('''
                INSERT INTO static_pages (slug, title, content, updated_at, updated_by)
                VALUES (?, ?, ?, datetime('now'), 'Admin')
                ON CONFLICT(slug) DO UPDATE SET
                    title = excluded.title,
                    content = excluded.content,
                    updated_at = datetime('now'),
                    updated_by = 'Admin'
            ''', (p['slug'], p['title'], p['content']))
            print(f"Updated/Inserted page: {p['slug']} in {db_path}")
        conn.commit()
        conn.close()
