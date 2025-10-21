# Plan de Implementación - Marketplace de Soluciones para Ciudades

## Visión General

Este proyecto se dividirá en **5 Fases** incrementales, cada una con entregables validables. El objetivo es tener un MVP funcional en Railway al finalizar la Fase 4, y la Fase 5 para optimización.

**Tiempo estimado total:** 3-5 días de desarrollo (full-time)

---

## Fase 0: Setup Inicial (Prerequisitos)
**Duración estimada:** 30 minutos
**Responsable:** Infra Agent

### Checklist
- [ ] Verificar acceso a Railway account
- [ ] Verificar acceso a Auth0 tenant
- [ ] Obtener credenciales Auth0 (Domain, Client ID, Secret)
- [ ] Crear repo GitHub vacío: `hivepress-marketplace`
- [ ] Clonar repo localmente
- [ ] Crear estructura de carpetas base (ver ARCHITECTURE.md)
- [ ] Crear `.env.example` con variables template
- [ ] Commit inicial: "chore: project scaffolding"

### Entregables
✅ Repo Git con estructura base
✅ `.env.example` documentado
✅ `README.md` con instrucciones de setup

---

## Fase 1: WordPress Base + Docker
**Duración estimada:** 4-6 horas
**Responsable:** DevOps Agent

### Objetivos
1. Crear Dockerfile funcional con WordPress
2. Configurar `wp-config.php` con env vars
3. Deploy manual en Railway (primera vez)
4. Validar conexión a MySQL

### Tareas Detalladas

#### 1.1 Dockerfile
```dockerfile
# docker/Dockerfile
FROM wordpress:6.4-apache

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Install PHP extensions (si faltan)
RUN docker-php-ext-install mysqli

# Copy custom config
COPY config/wp-config.php /var/www/html/wp-config.php

# Copy entrypoint script
COPY scripts/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
```

#### 1.2 wp-config.php (con env vars)
```php
<?php
define('DB_NAME', getenv('MYSQLDATABASE'));
define('DB_USER', getenv('MYSQLUSER'));
define('DB_PASSWORD', getenv('MYSQLPASSWORD'));
define('DB_HOST', getenv('MYSQLHOST') . ':' . getenv('MYSQLPORT'));
define('DB_CHARSET', 'utf8mb4');

define('WP_HOME', getenv('WP_HOME'));
define('WP_SITEURL', getenv('WP_SITEURL'));

// Security keys (from env)
define('AUTH_KEY', getenv('AUTH_KEY'));
// ... (resto de keys)

$table_prefix = 'wp_';
define('WP_DEBUG', getenv('WP_DEBUG') === 'true');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
```

#### 1.3 Entrypoint Script
```bash
#!/bin/bash
# scripts/entrypoint.sh

# Wait for MySQL
while ! mysqladmin ping -h"$MYSQLHOST" --silent; do
    echo "Waiting for MySQL..."
    sleep 2
done

# Check if WP is installed
if ! wp core is-installed --allow-root; then
    echo "Installing WordPress..."
    wp core install \
        --url="$WP_HOME" \
        --title="Marketplace de Soluciones" \
        --admin_user="admin" \
        --admin_password="$WP_ADMIN_PASSWORD" \
        --admin_email="$WP_ADMIN_EMAIL" \
        --allow-root
fi

# Start Apache
exec "$@"
```

#### 1.4 Railway Setup
- Crear nuevo proyecto en Railway
- Agregar MySQL service
- Agregar WordPress service (from Dockerfile)
- Configurar variables de entorno en Railway dashboard:
  ```
  WP_HOME=https://{generated-url}.railway.app
  WP_SITEURL=https://{generated-url}.railway.app
  WP_ADMIN_PASSWORD=xxxxx
  WP_ADMIN_EMAIL=xxx@xxx.com
  AUTH_KEY=... (generar con https://api.wordpress.org/secret-key/1.1/salt/)
  ```
- Configurar Railway Volume: `/var/www/html/wp-content/uploads`
- Deploy manual (primera vez)

### Validación
- [ ] WordPress accesible en `{app}.railway.app`
- [ ] Login admin funcional en `/wp-admin`
- [ ] MySQL conectado (no errores de DB)
- [ ] Uploads folder persistente (subir imagen de prueba, redeploy, verificar que no se pierde)

### Entregables
✅ WordPress funcional en Railway
✅ MySQL persistente
✅ Dockerfile + scripts en repo
✅ Documentación en `docs/DEPLOYMENT.md`

---

## Fase 2: HivePress + Campos Custom
**Duración estimada:** 6-8 horas
**Responsable:** Backend Agent

### Objetivos
1. Instalar HivePress vía WP-CLI
2. Configurar campos custom (LinkedIn Founders, etc.)
3. Crear 5 categorías
4. Crear child theme base

### Tareas Detalladas

#### 2.1 Script de Instalación de Plugins
```bash
# scripts/install-plugins.sh
#!/bin/bash

wp plugin install hivepress --activate --allow-root
wp plugin install polylang --activate --allow-root

# Verificar instalación
wp plugin list --allow-root
```

#### 2.2 Configurar Campos Custom
```php
// scripts/configure-hivepress.php
<?php
// Run with: wp eval-file configure-hivepress.php --allow-root

add_filter('hivepress/v1/models/listing', function($model) {

    // LinkedIn Founders (repeater)
    $model['fields']['linkedin_founders'] = [
        'label' => __('LinkedIn Founders', 'hivepress-child'),
        'type' => 'repeater',
        'max_items' => 5,
        'fields' => [
            'url' => [
                'label' => __('Profile URL', 'hivepress-child'),
                'type' => 'url',
                'placeholder' => 'https://linkedin.com/in/username',
            ],
        ],
        '_alias' => 'linkedin_founders',
    ];

    // Website (required)
    $model['fields']['website'] = [
        'label' => __('Website', 'hivepress-child'),
        'type' => 'url',
        'required' => true,
        'placeholder' => 'https://ejemplo.com',
        '_alias' => 'website',
    ];

    // Contact Email (optional)
    $model['fields']['contact_email'] = [
        'label' => __('Email de Contacto', 'hivepress-child'),
        'type' => 'email',
        'required' => false,
        '_alias' => 'contact_email',
    ];

    // Phone (optional)
    $model['fields']['phone'] = [
        'label' => __('Teléfono', 'hivepress-child'),
        'type' => 'text',
        'required' => false,
        '_alias' => 'phone',
    ];

    return $model;
});

// Save to trigger registration
update_option('hivepress_custom_fields_configured', true);
echo "Custom fields configured!\n";
```

#### 2.3 Crear Categorías
```php
// scripts/seed-categories.php
<?php
// Run with: wp eval-file seed-categories.php --allow-root

$categories = [
    'Movilidad' => 'Soluciones de transporte y movilidad urbana',
    'Espacio Público' => 'Gestión y mejora de espacios públicos',
    'Fintech' => 'Tecnología financiera para ciudades',
    'LegalIA' => 'Inteligencia artificial aplicada a lo legal',
    'Datos' => 'Análisis y visualización de datos urbanos',
];

foreach ($categories as $name => $description) {
    $exists = term_exists($name, 'hp_listing_category');

    if (!$exists) {
        wp_insert_term($name, 'hp_listing_category', [
            'description' => $description,
            'slug' => sanitize_title($name),
        ]);
        echo "Created: $name\n";
    } else {
        echo "Already exists: $name\n";
    }
}
```

#### 2.4 Child Theme Base
```php
// wp-content/themes/hivepress-child/style.css
/*
Theme Name: HivePress Child - Marketplace
Template: hivepress
Version: 1.0.0
*/

:root {
    --hp-primary-color: #0A2463;
    --hp-secondary-color: #3E92CC;
    --hp-background-color: #F8F9FA;
}

/* Importar parent theme */
@import url('../hivepress/style.css');

/* Custom styles */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.hp-listing__badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.hp-listing__badge--pending {
    background-color: #FFA500;
    color: white;
}

.hp-listing__badge--verified {
    background-color: #28A745;
    color: white;
}
```

```php
// wp-content/themes/hivepress-child/functions.php
<?php

// Enqueue parent theme styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('hivepress-parent', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('hivepress-child', get_stylesheet_uri(), ['hivepress-parent']);
});

// Add badge to listing title
add_filter('hivepress/v1/templates/listing_view_block', function($template) {
    $listing = $template['blocks']['listing_container']['blocks']['listing'];

    // Add badge after title
    $listing['blocks']['listing_badge'] = [
        'type' => 'part',
        'path' => 'listing/view/listing-badge',
        '_order' => 5,
    ];

    return $template;
});

// Include custom fields config
require_once __DIR__ . '/includes/custom-fields.php';
```

#### 2.5 Update entrypoint.sh
```bash
# Add after wp core install:

if ! wp option get hivepress_installed --allow-root; then
    echo "Setting up HivePress..."
    bash /var/www/html/scripts/install-plugins.sh
    wp eval-file /var/www/html/scripts/configure-hivepress.php --allow-root
    wp eval-file /var/www/html/scripts/seed-categories.php --allow-root

    # Activate child theme
    wp theme activate hivepress-child --allow-root

    wp option update hivepress_installed true --allow-root
fi
```

### Validación
- [ ] HivePress activado
- [ ] 5 categorías creadas en `/wp-admin/edit-tags.php?taxonomy=hp_listing_category`
- [ ] Campos custom visibles al crear listing
- [ ] Child theme activado
- [ ] Colores azul marino aplicados

### Entregables
✅ HivePress configurado
✅ Campos custom funcionales
✅ Child theme con branding
✅ Scripts automatizados en repo

---

## Fase 3: Auth0 Integration
**Duración estimada:** 6-8 horas
**Responsable:** Security Agent

### Objetivos
1. Crear plugin custom Auth0 integration
2. Login con Auth0 funcional
3. Auto-creación de usuarios como Contributor
4. Desactivar registro nativo de WordPress

### Tareas Detalladas

#### 3.1 Plugin Base Structure
```
wp-content/plugins/hivepress-auth0/
├── hivepress-auth0.php
├── includes/
│   ├── class-auth0-client.php
│   ├── class-user-manager.php
│   └── class-login-handler.php
└── README.md
```

#### 3.2 Plugin Principal
```php
// wp-content/plugins/hivepress-auth0/hivepress-auth0.php
<?php
/**
 * Plugin Name: HivePress Auth0 Integration
 * Description: Auth0 SSO for HivePress marketplace
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/includes/class-auth0-client.php';
require_once __DIR__ . '/includes/class-user-manager.php';
require_once __DIR__ . '/includes/class-login-handler.php';

// Initialize
add_action('plugins_loaded', function() {
    new HivePress_Auth0_Login_Handler();
});

// Redirect WP login to Auth0
add_action('login_init', function() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'logout') {
        HivePress_Auth0_Login_Handler::redirect_to_auth0();
    }
});

// Disable native registration
add_filter('wp_authenticate_user', function($user) {
    if (!get_user_meta($user->ID, 'auth0_id', true)) {
        return new WP_Error('auth0_only', 'Please login with Auth0');
    }
    return $user;
}, 10, 1);
```

#### 3.3 Auth0 Client (OAuth)
```php
// includes/class-auth0-client.php
<?php
class HivePress_Auth0_Client {

    private $domain;
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    public function __construct() {
        $this->domain = getenv('AUTH0_DOMAIN');
        $this->client_id = getenv('AUTH0_CLIENT_ID');
        $this->client_secret = getenv('AUTH0_CLIENT_SECRET');
        $this->redirect_uri = getenv('AUTH0_REDIRECT_URI');
    }

    public function get_authorization_url() {
        $params = [
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'openid profile email',
        ];

        return "https://{$this->domain}/authorize?" . http_build_query($params);
    }

    public function exchange_code($code) {
        // Exchange code for tokens
        $response = wp_remote_post("https://{$this->domain}/oauth/token", [
            'body' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $code,
                'redirect_uri' => $this->redirect_uri,
            ],
        ]);

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function get_user_info($access_token) {
        $response = wp_remote_get("https://{$this->domain}/userinfo", [
            'headers' => [
                'Authorization' => "Bearer {$access_token}",
            ],
        ]);

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
```

#### 3.4 User Manager
```php
// includes/class-user-manager.php
<?php
class HivePress_Auth0_User_Manager {

    public static function find_or_create_user($auth0_user) {
        // Search by auth0_id
        $users = get_users([
            'meta_key' => 'auth0_id',
            'meta_value' => $auth0_user['sub'],
            'number' => 1,
        ]);

        if (!empty($users)) {
            return $users[0];
        }

        // Create new user
        $user_id = wp_create_user(
            sanitize_user($auth0_user['nickname']),
            wp_generate_password(),
            $auth0_user['email']
        );

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Set role to Contributor
        $user = new WP_User($user_id);
        $user->set_role('contributor');

        // Store Auth0 data
        update_user_meta($user_id, 'auth0_id', $auth0_user['sub']);
        update_user_meta($user_id, 'auth0_profile', $auth0_user);

        // Set display name
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $auth0_user['name'] ?? $auth0_user['nickname'],
        ]);

        return $user;
    }
}
```

#### 3.5 Railway Env Vars
Agregar en Railway dashboard:
```
AUTH0_DOMAIN=xxx.us.auth0.com
AUTH0_CLIENT_ID=xxxxxxxxx
AUTH0_CLIENT_SECRET=xxxxxxxxx
AUTH0_REDIRECT_URI=https://{app}.railway.app/wp-login.php
```

### Validación
- [ ] Login redirige a Auth0
- [ ] Autenticación exitosa crea usuario en WordPress
- [ ] Usuario creado tiene rol "Contributor"
- [ ] Metadata `auth0_id` guardado correctamente
- [ ] Registro nativo deshabilitado

### Entregables
✅ Plugin Auth0 funcional
✅ Auto-creación de usuarios
✅ Login flow documentado

---

## Fase 4: Frontend Customization
**Duración estimada:** 8-10 horas
**Responsable:** Frontend Agent

### Objetivos
1. Customizar home page (6 listings random)
2. Agregar buscador
3. Mostrar badges de verificación
4. Página "Sobre Nosotros"
5. Traducción bilingüe (ES/EN)

### Tareas Detalladas

#### 4.1 Home Page Template
```php
// wp-content/themes/hivepress-child/templates/home.php
<?php get_header(); ?>

<div class="hp-marketplace-home">

    <!-- Hero Section -->
    <section class="hero">
        <h1><?php _e('Marketplace de Soluciones para Ciudades', 'hivepress-child'); ?></h1>
        <p><?php _e('Conectamos innovación con necesidades urbanas', 'hivepress-child'); ?></p>
    </section>

    <!-- Search Bar -->
    <section class="search-section">
        <?php echo do_shortcode('[hivepress_search_form]'); ?>
    </section>

    <!-- Categories Buttons -->
    <section class="categories-section">
        <div class="category-buttons">
            <?php
            $categories = get_terms(['taxonomy' => 'hp_listing_category']);
            foreach ($categories as $cat) {
                echo sprintf(
                    '<a href="%s" class="category-btn">%s</a>',
                    get_term_link($cat),
                    esc_html($cat->name)
                );
            }
            ?>
        </div>
    </section>

    <!-- Random Listings (2x3 grid) -->
    <section class="listings-grid">
        <?php
        $listings = get_posts([
            'post_type' => 'hp_listing',
            'posts_per_page' => 6,
            'orderby' => 'rand',
            'post_status' => ['publish', 'pending'], // Both statuses
        ]);

        foreach ($listings as $post) {
            setup_postdata($post);
            get_template_part('templates/listing/view/listing-card');
        }
        wp_reset_postdata();
        ?>
    </section>

</div>

<?php get_footer(); ?>
```

#### 4.2 Listing Card con Badge
```php
// wp-content/themes/hivepress-child/templates/listing/view/listing-card.php
<?php
$status = get_post_status();
$badge_class = $status === 'publish' ? 'verified' : 'pending';
$badge_text = $status === 'publish' ? __('Verificada', 'hivepress-child') : __('Sin Verificar', 'hivepress-child');
?>

<article class="listing-card">
    <a href="<?php the_permalink(); ?>">
        <?php if (has_post_thumbnail()): ?>
            <div class="listing-card__image">
                <?php the_post_thumbnail('medium'); ?>
                <span class="hp-listing__badge hp-listing__badge--<?php echo $badge_class; ?>">
                    <?php echo $badge_text; ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="listing-card__content">
            <h3><?php the_title(); ?></h3>

            <?php
            $category = wp_get_post_terms(get_the_ID(), 'hp_listing_category');
            if (!empty($category)) {
                echo '<span class="listing-card__category">' . esc_html($category[0]->name) . '</span>';
            }
            ?>

            <p><?php echo wp_trim_words(get_the_content(), 20); ?></p>
        </div>
    </a>
</article>
```

#### 4.3 Detalle de Listing (con LinkedIn Founders)
```php
// wp-content/themes/hivepress-child/templates/listing/view/single-listing.php
<?php
get_header();

while (have_posts()): the_post();
    $listing_id = get_the_ID();
    $status = get_post_status();
    $badge_class = $status === 'publish' ? 'verified' : 'pending';
    $badge_text = $status === 'publish' ? __('Verificada', 'hivepress-child') : __('Sin Verificar', 'hivepress-child');
?>

<article class="listing-single">

    <header class="listing-header">
        <h1><?php the_title(); ?></h1>
        <span class="hp-listing__badge hp-listing__badge--<?php echo $badge_class; ?>">
            <?php echo $badge_text; ?>
        </span>
    </header>

    <!-- Image Gallery (up to 4) -->
    <div class="listing-gallery">
        <?php
        $images = get_post_meta($listing_id, 'hp_images', true);
        if (!empty($images) && is_array($images)) {
            foreach (array_slice($images, 0, 4) as $image_id) {
                echo wp_get_attachment_image($image_id, 'large');
            }
        }
        ?>
    </div>

    <!-- Description -->
    <div class="listing-description">
        <?php the_content(); ?>
    </div>

    <!-- Contact Info -->
    <aside class="listing-contact">
        <h3><?php _e('Información de Contacto', 'hivepress-child'); ?></h3>

        <?php $website = get_post_meta($listing_id, 'hp_website', true); ?>
        <?php if ($website): ?>
            <p><strong>Web:</strong> <a href="<?php echo esc_url($website); ?>" target="_blank"><?php echo esc_html($website); ?></a></p>
        <?php endif; ?>

        <?php $email = get_post_meta($listing_id, 'hp_contact_email', true); ?>
        <?php if ($email): ?>
            <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
        <?php endif; ?>

        <?php $phone = get_post_meta($listing_id, 'hp_phone', true); ?>
        <?php if ($phone): ?>
            <p><strong>Teléfono:</strong> <?php echo esc_html($phone); ?></p>
        <?php endif; ?>
    </aside>

    <!-- LinkedIn Founders -->
    <?php
    $founders = get_post_meta($listing_id, 'hp_linkedin_founders', true);
    if (!empty($founders) && is_array($founders)):
    ?>
        <section class="listing-founders">
            <h3><?php _e('Founders', 'hivepress-child'); ?></h3>
            <ul>
                <?php foreach ($founders as $founder): ?>
                    <?php if (!empty($founder['url'])): ?>
                        <li><a href="<?php echo esc_url($founder['url']); ?>" target="_blank">LinkedIn Profile</a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

</article>

<?php
endwhile;
get_footer();
?>
```

#### 4.4 Polylang Setup
```bash
# In entrypoint.sh, after HivePress setup:

if ! wp option get polylang_configured --allow-root; then
    # Configure languages
    wp pll lang create es "Español" es_ES --allow-root
    wp pll lang create en "English" en_US --allow-root
    wp pll option update default_lang es --allow-root

    wp option update polylang_configured true --allow-root
fi
```

#### 4.5 Translation Files
```
wp-content/themes/hivepress-child/languages/
├── es_ES.po
└── en_US.po
```

Usar WP-CLI para generar:
```bash
wp i18n make-pot wp-content/themes/hivepress-child \
    wp-content/themes/hivepress-child/languages/hivepress-child.pot --allow-root
```

#### 4.6 Página "Sobre Nosotros"
```bash
# En entrypoint.sh:
if ! wp post exists --post_type=page --post_title="Sobre Nosotros" --allow-root; then
    wp post create \
        --post_type=page \
        --post_title="Sobre Nosotros" \
        --post_content="Texto Sobre Nosotros" \
        --post_status=publish \
        --allow-root
fi
```

### Validación
- [ ] Home muestra 6 listings random
- [ ] Buscador funcional
- [ ] Botones de categorías filtran correctamente
- [ ] Badges visibles en cards y detalle
- [ ] Detalle muestra hasta 4 imágenes
- [ ] LinkedIn Founders se renderizan como links
- [ ] Página "Sobre Nosotros" creada
- [ ] Switcher de idioma ES/EN funcional

### Entregables
✅ Templates custom completos
✅ CSS responsive
✅ Traducción bilingüe
✅ UX validada

---

## Fase 5: Testing y Optimización
**Duración estimada:** 4-6 horas
**Responsable:** QA Agent + DevOps Agent

### Objetivos
1. Testing E2E completo
2. Performance optimization
3. Documentación final
4. Deploy definitivo

### Tareas Detalladas

#### 5.1 Testing Checklist

##### Auth Flow
- [ ] Login con Auth0 exitoso
- [ ] Usuario nuevo se crea como Contributor
- [ ] Admin puede login con credenciales nativas
- [ ] Logout funciona correctamente

##### Listing CRUD
- [ ] Contributor puede crear listing (queda pending)
- [ ] Contributor puede editar su propio listing
- [ ] Admin puede aprobar listing (pending → publish)
- [ ] Badges se actualizan correctamente

##### Frontend
- [ ] Home carga 6 listings random
- [ ] Categorías filtran correctamente
- [ ] Buscador encuentra por título/descripción
- [ ] Detalle muestra todos los campos
- [ ] Imágenes se cargan (hasta 4)
- [ ] LinkedIn Founders son clicables
- [ ] Responsive en mobile (320px, 768px, 1024px)

##### i18n
- [ ] Switcher ES/EN funciona
- [ ] Strings traducidos correctamente
- [ ] Contenido se guarda en idioma seleccionado

##### Performance
- [ ] TTFB < 1s
- [ ] Page size < 2MB
- [ ] Images optimizadas
- [ ] No errores en consola

#### 5.2 Performance Optimizations

```bash
# Install cache plugin
wp plugin install wp-super-cache --activate --allow-root

# Configure cache
wp super-cache enable --allow-root
```

```php
// wp-content/mu-plugins/performance.php
<?php
// Disable unnecessary features
add_filter('wp_revisions_to_keep', function() { return 5; });
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
```

#### 5.3 Documentación Final

Crear/actualizar:
- `README.md` - Instrucciones de setup
- `docs/DEPLOYMENT.md` - Pasos de deploy en Railway
- `docs/USER_GUIDE.md` - Guía para admin (crear listings, aprobar, etc.)
- `docs/TROUBLESHOOTING.md` - Errores comunes

#### 5.4 Deploy Definitivo

```bash
# Tag release
git tag -a v1.0.0 -m "MVP Release"
git push origin v1.0.0

# Railway auto-deploy from main
# Verify in Railway dashboard
```

### Validación Final
- [ ] Todos los tests pasan
- [ ] Performance OK
- [ ] Documentación completa
- [ ] Railway URL estable

### Entregables
✅ Sistema testeado E2E
✅ Performance optimizado
✅ Documentación completa
✅ **MVP en producción**

---

## Cronograma Visual

```
Semana 1
├── Día 1: Fase 0 + Fase 1 (WordPress base)
├── Día 2: Fase 2 (HivePress + campos)
├── Día 3: Fase 3 (Auth0)
├── Día 4: Fase 4 (Frontend)
└── Día 5: Fase 5 (Testing + Deploy)

MILESTONE: MVP Live 🚀
```

---

## Risks & Mitigation

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Railway quota limits | Media | Alto | Monitorear uso, upgrade si necesario |
| Auth0 callback issues | Media | Alto | Testear en local con ngrok primero |
| HivePress config errors | Baja | Medio | Seguir docs oficiales, backup DB |
| Image upload failures | Media | Bajo | Validar Railway Volume path |
| Translation strings missing | Alta | Bajo | Review manual de PO files |

---

## Post-Launch Roadmap

### Short-term (1-2 semanas)
- [ ] Agregar Google Analytics
- [ ] Setup dominio custom
- [ ] Monitoreo con UptimeRobot
- [ ] Backup automático de MySQL

### Medium-term (1-2 meses)
- [ ] Sistema de favoritos
- [ ] Email notifications (SendGrid)
- [ ] Export listings a CSV
- [ ] Panel de métricas para admin

### Long-term (3+ meses)
- [ ] API REST pública
- [ ] Mobile app (React Native?)
- [ ] Sistema de reviews
- [ ] Geolocalización (mapas)

---

## Resources & Links

- **Railway Docs:** https://docs.railway.app/
- **HivePress Docs:** https://hivepress.io/docs/
- **Auth0 WordPress:** https://auth0.com/docs/quickstart/webapp/wordpress
- **WP-CLI Handbook:** https://make.wordpress.org/cli/handbook/
- **Polylang Docs:** https://polylang.pro/doc/

---

**Estado actual:** Documentación completa ✅
**Siguiente paso:** Iniciar Fase 0 (Setup)
