<script src="<?php echo $base_url; ?>/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/libs/node-waves/waves.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/libs/feather-icons/feather.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script src="<?php echo $base_url; ?>/assets/js/plugins.js"></script>
<script src="<?php echo $base_url; ?>/assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/libs/jsvectormap/maps/world-merc.js"></script>
<script src="<?php echo $base_url; ?>/assets/libs/swiper/swiper-bundle.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/js/pages/dashboard-ecommerce.init.js"></script>
<script src="<?php echo $base_url; ?>/assets/js/app.js"></script>

<!-- SELECT2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/js/pages/select2.init.js"></script>

<script>
	/* COPY URL */
	function copyToClipboard() {
		var copyText = document.getElementById("qr_code");
		copyText.select();
		copyText.setSelectionRange(0, 99999)
		document.execCommand("copy");
	}
	function copyToClipboard2() {
		var copyText = document.getElementById("qr_code2");
		copyText.select();
		copyText.setSelectionRange(0, 99999)
		document.execCommand("copy");
	}

	/*IC FUNCTION*/
    document.addEventListener('DOMContentLoaded', () => {
        for (const el of document.querySelectorAll("[placeholder][data-slots]")) {
            const pattern = el.getAttribute("placeholder"),
                slots = new Set(el.dataset.slots || "_"),
                prev = (j => Array.from(pattern, (c,i) => slots.has(c)? j=i+1: j))(0),
                first = [...pattern].findIndex(c => slots.has(c)),
                accept = new RegExp(el.dataset.accept || "\\d", "g"),
                clean = input => {
                    input = input.match(accept) || [];
                    return Array.from(pattern, c =>
                        input[0] === c || slots.has(c) ? input.shift() || c : c
                    );
                },
                format = () => {
                    const [i, j] = [el.selectionStart, el.selectionEnd].map(i => {
                        i = clean(el.value.slice(0, i)).findIndex(c => slots.has(c));
                        return i<0? prev[prev.length-1]: back? prev[i-1] || first: i;
                    });
                    el.value = clean(el.value).join``;
                    el.setSelectionRange(i, j);
                    back = false;
                };
            let back = false;
            el.addEventListener("keydown", (e) => back = e.key === "Backspace");
            el.addEventListener("input", format);
            el.addEventListener("focus", format);
            el.addEventListener("blur", () => el.value === pattern && (el.value=""));
        }
    });
</script>