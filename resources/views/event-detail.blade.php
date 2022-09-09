@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Area -->
    <div class="tm-breadcrumb-area tm-padding-section bg-gradient">
        <div class="tm-breadcrumb-bgshape">
            <img src="assets/images/download-bgshape.png" alt="bg shape">
        </div>
        <div class="container">
            <div class="tm-breadcrumb text-center">
                <h2>Event Details</h2>
            </div>
        </div>
    </div>
    <!--// Breadcrumb Area -->

    <!-- Page Content -->
    <main class="page-content">

        <!-- Blog Area -->
        <div class="tm-section blogs-area bg-whtie tm-padding-section">
            <div class="container">
                <div class="tm-blog blogitem">
                    <div class="tm-blog-topside">
                        <div class="tm-blog-image">
                            <img src="assets/images/blog-image-lg-1.jpg" alt="appmyil blog image">
                        </div>
                        <span class="tm-blog-date">September 16, 2019</span>
                    </div>
                    <div class="tm-blog-content">
                        <div class="tm-blog-meta">
                            <span><a href="blog-without-sidebar.html"><i class="zmdi zmdi-account"></i> Admin</a></span>
                            <span><a href="blog-details-without-sidebar.html"><i class="zmdi zmdi-comments"></i>
                                    Comments (3)</a></span>
                            <span><a href="blog-without-sidebar.html"><i class="zmdi zmdi-folder"></i> Services</a></span>
                        </div>
                        <h4 class="tm-blog-title">How To Active New Features In Current App?</h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque mi dolor,
                            malesuada id metus a, mattis eleifend elit. Nullam pharetra consequat ex in
                            dapibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices
                            posuere
                            cubilia Curae; Vivamus lacinia dui tellus. Donec condimentum vel diam eget
                            accumsan. Fusce sit amet nunc fermentum, mattis lacus eu, euismod ipsum.
                            Nam tincidunt leo sed lorem ultricies molestie. In in turpis id risus
                            lacinia finibus eget
                            eget eros. Mauris lobortis, tortor eu ornare conva mauris tortor blandit
                            orci, nec eleifend velit dolor sit amet nunc.
                            Suspendisse vel ipsum tempor, volutpat arcuat, faucibus mi. In efficitur
                            lorem.</p>
                        <blockquote>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Harum, eos
                                magni! Laudantium illum, reprehenderit distinctio expedita voluptates
                                eligendi fugiat voluptatibus nostrum tenetur modi libero.</p>
                        </blockquote>
                        <p>Nunc tincidunt id eros non euismod. Sed tellus erat, varius et sollicitudin
                            non, ullamcorper eget arcu. Mauris at lectus aliquet, vestibulum dui non,
                            volutpat orci. Integer dapibus tincidunt ornare. Praesent blandit leo at
                            turpis porta, a
                            dignissim nunc consequat. Etiam eu varius enim, et varius lacus. Integer
                            tellus tellus, dictum ac pellentesque quis,
                            scelerisque at ligula. Maecenas dignissim lectus a mauris vulputate
                            fermentum. Suspendisse potenti. Duis id cursus
                            augue, vitae scelerisque metus.</p>
                    </div>

                    <div class="blogitem-tags">
                        <h5 class="blogitem-tags-title">Tags:</h5>
                        <ul>
                            <li><a href="blog-without-sidebar.html">Apps</a></li>
                            <li><a href="blog-without-sidebar.html">Development</a></li>
                            <li><a href="blog-without-sidebar.html">Php</a></li>
                            <li><a href="blog-without-sidebar.html">Web</a></li>
                        </ul>
                    </div>

                    <div class="blogitem-share">
                        <h5 class="blogitem-share-title">Share With: </h5>
                        <ul>
                            <li class="facebook"><a href="https://www.facebook.com/sharer/sharer.php?u="><i
                                        class="zmdi zmdi-facebook"></i>
                                    Facebook</a></li>
                            <li class="twitter"><a href="https://twitter.com/home?status="><i class="zmdi zmdi-twitter"></i>
                                    Twitter</a></li>
                            <li class="pinterest"><a
                                    href="https://pinterest.com/pin/create/button/?url=&media=&description="><i
                                        class="zmdi zmdi-pinterest"></i> Pinterest</a></li>
                            <li class="linkedin"><a
                                    href="https://www.linkedin.com/shareArticle?mini=true&url=&title=&summary=&source="><i
                                        class="zmdi zmdi-linkedin"></i> Linkedin</a></li>
                        </ul>
                    </div>

                    <!-- Blogitem Comments -->
                    <div class="blogitem-comments">
                        <h5 class="small-title">Comments (2)</h5>

                        <div class="tm-comment-wrapper mt-30">

                            <!-- Comment Single -->
                            <div class="tm-comment">
                                <a href="#" class="tm-comment-thumb">
                                    <img src="assets/images/author-image-4.jpg" alt="author image">
                                </a>
                                <div class="tm-comment-content">
                                    <h6 class="tm-comment-authorname"><a href="#">Dorris Goyette</a></h6>
                                    <span class="tm-comment-date">Wednesday, October 17, 2019 at
                                        4:00PM.</span>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit,
                                        sed do
                                        eiusmod
                                        tempor incididunt ut labore dolore magna aliqua. Ut enim ad
                                        minim
                                        veniam.</p>
                                    <a href="#" class="tm-comment-replybutton"><i
                                            class="zmdi zmdi-mail-reply-all"></i>
                                        Reply</a>
                                </div>
                            </div>
                            <!--// Comment Single -->

                            <!-- Comment Single -->
                            <div class="tm-comment tm-comment-replypost">
                                <a href="#" class="tm-comment-thumb">
                                    <img src="assets/images/author-image-2.jpg" alt="author image">
                                </a>
                                <div class="tm-comment-content">
                                    <h6 class="tm-comment-authorname"><a href="#">Gladys Zulauf</a></h6>
                                    <span class="tm-comment-date">Wednesday, October 17, 2019 at
                                        4:00PM.</span>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit,
                                        sed do
                                        eiusmod
                                        tempor dolore magna aliqua. Ut enim ad minim
                                        veniam.</p>
                                    <a href="#" class="tm-comment-replybutton"><i
                                            class="zmdi zmdi-mail-reply-all"></i>
                                        Reply</a>
                                </div>
                            </div>
                            <!--// Comment Single -->

                            <!-- Comment Single -->
                            <div class="tm-comment">
                                <a href="#" class="tm-comment-thumb">
                                    <img src="assets/images/author-image-3.jpg" alt="author image">
                                </a>
                                <div class="tm-comment-content">
                                    <h6 class="tm-comment-authorname"><a href="#">Kareem Todd</a></h6>
                                    <span class="tm-comment-date">Wednesday, October 17, 2019 at
                                        4:00PM.</span>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit,
                                        sed do
                                        eiusmod
                                        tempor incididunt ut labore dolore magna aliqua. Ut enim ad
                                        minim
                                        veniam.</p>
                                    <a href="#" class="tm-comment-replybutton"><i
                                            class="zmdi zmdi-mail-reply-all"></i>
                                        Reply</a>
                                </div>
                            </div>
                            <!--// Comment Single -->

                        </div>

                    </div>
                    <!--// Blogitem Comments -->

                    <!-- Blogitem Commentbox -->
                    <div class="blogitem-commentbox mt-30">
                        <h5 class="small-title">Leave a Comment </h5>
                        <p>Your email address will not be published. Required fields are
                            marked *</p>
                        <form action="#" class="tm-form mt-30">
                            <div class="tm-form-inner">
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="text" id="tm-comment-namefield" placeholder="Name*">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field tm-form-fieldhalf">
                                    <input type="email" id="tm-comment-email" placeholder="Email*">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field">
                                    <input type="text" id="tm-comment-website" placeholder="Website">
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field">
                                    <textarea name="tm-comment-textbox" id="tm-comment-textbox" cols="30" rows="7"
                                        placeholder="Enter your comment"></textarea>
                                    <span class="tm-form-animatedline"></span>
                                </div>
                                <div class="tm-form-field">
                                    <button type="submit" class="tm-button tm-button-dark"><span>Post
                                            Comment</span></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--// Blogitem Commentbox -->
                </div>
            </div>
        </div>
        <!--// Blog Area -->

    </main>
    <!--// Page Content -->
@endsection
