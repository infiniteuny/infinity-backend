<!-- FOOTER SECTION START -->
<footer class="footer-section" id="footer-section">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-5">
                <h3 class="text-dark">Temukan Kami</h3>
                <a href="{{ $config['ADDRESS_URL'] }}">{!! $config['ADDRESS_NAME'] !!} </a>
            </div>

            <div class="col-md-6">
                <h3 class="text-dark">SOCIAL MEDIA</h3>
                <a href="{{ $config['DISCORD_URL'] }}" target="blank" class="fab fa-discord"></a><span> &nbsp;{{ $config['DISCORD_NAME'] }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
                <a href="{{ $config['TWITTER_URL'] }}" target="blank" class="fab fa-twitter"></a><span> &nbsp;{{ $config['TWITTER_NAME'] }}&nbsp;&nbsp;</span> <br><br>
                <a href="{{ $config['INSTAGRAM_URL'] }}" target="blank" class="fab fa-instagram"></a><span> &nbsp;{{ $config['INSTAGRAM_NAME'] }}&nbsp;&nbsp;&nbsp;</span>
                <a href="{{ $config['WHATSAPP_URL'] }}" target="blank" class="fab fa-whatsapp"></a><span> &nbsp;{{ $config['WHATSAPP_NAME'] }}</span>
            </div>
        </div>
        <div class="row mt-5 text-center footer-end">
            <div class="col-md-12">
                <p class="copyright">
                    <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    Copyright &copy;<script>document.write(new Date().getFullYear());</script> </a>
                    <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                </p>
            </div>
        </div>
    </div>   
</footer>
<!-- FOOTER SECTION END -->