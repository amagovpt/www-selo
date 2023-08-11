<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> >

    <header id="wrapper-navbar">
		<div class="skip-to-content">
			<div class="container">
				<a class="skip-to-content-link" href="#content"><span>Saltar para o conteúdo principal da página</span></a>
			</div>
		</div>

		<div class="top-bar">
			<div class="accordion accordion-flush" id="accordionTopBar">
				<div class="container">
					<div class="d-flex justify-content-between">
						<button type="button" class="btn btn-link btn-ecossistema collapsed d-flex align-items-center p-1" data-bs-toggle="collapse" data-bs-target="#flushEcossistema"  aria-expanded="false" aria-controls="flushEcossistema">
							<span class="icon-ed-menu-dots icon" aria-hidden="true"></span>	
							<span id="flushHeading">Uma ferramenta do ecossistema <span class="text-primary fw-bold">acessibilidade.gov.pt</span></span>
							<span class="icon-arrow-drop icon" aria-hidden="true"></span>
						</button>
						<button class="btn btn-link dark-mode p-1 d-flex align-items-center" id="darkModeBtn">
							<span id="darkModeLabel">Modo Escuro</span><span class="icon-dark icon" aria-hidden="true"></span>
						</button>
					</div>
					<div id="flushEcossistema" class="accordion-collapse collapse" aria-labelledby="flushHeading" data-bs-parent="#accordionTopBar">
						<div class="accordion-body">
							<div class="container">
								<div class="row">
									<div class="col-12 col-lg-6 align-self-center">
										<div class="d-flex justify-content-between">
											<?php dynamic_sidebar( 'title-ecossistema-ama' ); ?>
										</div>
									</div>
									<div class="col-12 col-lg-6 align-self-center">
										<?php dynamic_sidebar( 'description-ecossistema-ama' ); ?>
									</div>
								</div>
								<hr class="mt-4 mb-5">
								<div class="row">
									<div class="col-12 col-lg-4">
										<?php dynamic_sidebar( 'left-column-ecossistema-ama' ); ?>
									</div>
									<div class="col-12 col-lg-4">
										<?php dynamic_sidebar( 'center-column-ecossistema-ama' ); ?>
									</div>
									<div class="col-12 col-lg-4">
										<?php dynamic_sidebar( 'right-column-ecossistema-ama' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="widgets-bar">
			<div class="container">
				<div class="row">
					<div class="col-12 col-lg-6 align-self-center">
						<div class="d-flex justify-content-between">
							<?php if ( ! is_front_page() ) : ?>
								<a href="<?php echo site_url(); ?>">
							<?php endif; ?>
							<?php dynamic_sidebar( 'title' ); ?>
							<?php if ( ! is_front_page() ) : ?>
								</a>
							<?php endif; ?>
							<div class="d-flex d-lg-none flex-column align-items-center">
								<button type="button" class="open-mobile-menu">
									<span class="icon-hamburguer-menu-round icon" aria-hidden="true"></span>
									<span class="visually-hidden">Abrir menu de navegação</span>
								</button>
								<p class="mobile-menu-label">Menu</p>
							</div>
						</div>
					</div>
					<div class="col-12 col-lg-6 align-self-center">
						<?php dynamic_sidebar( 'description' ); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="navigation">
			<div class="container">
				<div class="mobile-overlay">
					<div class="mobile-overlay-content">
						<div class="widgets-bar-mobile d-lg-none">
							<div class="d-flex justify-content-between align-items-start">
								<?php if ( ! is_front_page() ) : ?>
									<a href="<?php echo site_url(); ?>">
								<?php endif; ?>
								<?php dynamic_sidebar( 'Title' ); ?>
								<?php if ( ! is_front_page() ) : ?>
									</a>
								<?php endif; ?>
								<div class="d-flex d-lg-none">
									<button type="button" class="close-mobile-menu">
										<span class="icon-close-round icon" aria-hidden="true"></span>
										<span class="visually-hidden">Fechar menu de navegação</span>
									</button>
								</div>
							</div>
						</div>
						<nav class="navbar py-0" aria-label="Menu principal do selo.usabilidade.gov.pt">
							<?php 
							wp_nav_menu(
								array(
									'theme_location'  => 'primary',
									'menu_class' => 'main-menu navbar-nav',
								)
							);
							?>
						</nav>
					</div>
				</div>
				<div class="search">
					<?php get_search_form(); ?>
				</div>
			</div>
		</div>

    </header>
