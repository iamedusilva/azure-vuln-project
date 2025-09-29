# Documentação CSS - FinSecure Web Application

## 📁 Estrutura dos Arquivos CSS

```
app/public/assets/css/
├── common.css      # Estilos comuns e utilitários
├── index.css       # Página inicial
├── auth.css        # Login e registro
├── dashboard.css   # Dashboard principal
└── README.md       # Esta documentação
```

## 🎨 Arquivos CSS Criados

### 1. `common.css` - Estilos Comuns
**Propósito**: Contém variáveis CSS, classes utilitárias e componentes reutilizáveis.

**Conteúdo Principal**:
- **Variáveis CSS**: Cores, gradientes, sombras, transições
- **Utility Classes**: Texto, espaçamento, display, flexbox
- **Team Section**: Seção completa da equipe com 5 membros
- **Glass Components**: Efeitos de vidro reutilizáveis
- **Animations**: Animações e keyframes
- **Custom Scrollbar**: Barra de rolagem personalizada

**Uso**: Importar em todas as páginas como base.

### 2. `index.css` - Página Inicial
**Propósito**: Estilos específicos para a página inicial pública.

**Conteúdo Principal**:
- Layout da página inicial
- Navigation buttons com gradientes
- Statistics cards com hover effects
- Recent activity section
- Team section integrada
- Responsive design completo

**Páginas**: `app/public/index.php`

### 3. `auth.css` - Autenticação
**Propósito**: Estilos para páginas de login e registro.

**Conteúdo Principal**:
- Container de login/registro
- Form styling com glass effect
- Password toggle functionality styling
- Test credentials section
- Injection examples styling
- Mobile-first responsive design

**Páginas**: `login.php`, `register.php`

### 4. `dashboard.css` - Dashboard
**Propósito**: Estilos complexos para o dashboard administrativo.

**Conteúdo Principal**:
- Navigation bar com dropdown
- Dashboard grid system
- Modal system completo
- Tables com sorting e filtering
- Password toggle components
- User data cards
- Comprehensive responsive design

**Páginas**: `dashboard.php`

## 🎯 Variáveis CSS (Custom Properties)

```css
:root {
    /* Cores principais */
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #00bcd4 0%, #1976d2 100%);
    --background-gradient: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
    
    /* Cores sólidas */
    --primary-color: #667eea;
    --secondary-color: #00bcd4;
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
    
    /* Estados */
    --success-color: #28a745;
    --warning-color: #ffc107;
    --error-color: #dc3545;
    
    /* Transparências */
    --glass-bg: rgba(255, 255, 255, 0.05);
    --glass-border: rgba(255, 255, 255, 0.1);
    
    /* Transições */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
}
```

## 👥 Seção da Equipe

A seção da equipe foi implementada no `common.css` e integrada no `index.css`:

```html
<div class="team-section">
    <h2 class="team-title">Nossa Equipe</h2>
    <p class="team-subtitle">Especialistas em Segurança da Informação</p>
    <div class="team-grid">
        <!-- 5 membros da equipe -->
    </div>
</div>
```

**Características**:
- 5 membros da equipe
- Avatar com iniciais
- Hover effects
- Responsive grid
- Glass effect design

## 📱 Design Responsivo

Todos os arquivos incluem breakpoints responsivos:

- **Desktop**: > 1200px
- **Large Tablet**: 992px - 1199px  
- **Tablet**: 768px - 991px
- **Mobile**: 576px - 767px
- **Small Mobile**: 360px - 575px
- **Extra Small**: < 360px

## 🎨 Paleta de Cores

### Cores Principais
- **Primary**: #667eea (FinSecure Blue)
- **Secondary**: #00bcd4 (Cyan)
- **Accent**: #1976d2 (Blue)

### Gradientes
- **Primary Gradient**: #667eea → #764ba2
- **Secondary Gradient**: #00bcd4 → #1976d2  
- **Background**: #0a0a0a → #1a1a2e → #16213e

### Estados
- **Success**: #28a745
- **Warning**: #ffc107
- **Error**: #dc3545

## ⚡ Animações e Efeitos

### Keyframes Disponíveis
- `float`: Background animation
- `slideUp`: Entry animation
- `fadeIn`: Fade animation
- `spin`: Loading spinner
- `pulse`: Attention animation

### Utility Classes
- `.animate-fadeIn`
- `.animate-slideInLeft`
- `.animate-slideInRight`
- `.animate-pulse`
- `.animate-delay-1` até `.animate-delay-5`

## 🔧 Classes Utilitárias

### Texto
- `.text-center`, `.text-left`, `.text-right`
- `.text-primary`, `.text-secondary`, `.text-muted`

### Espaçamento
- `.mb-0` até `.mb-5` (margin-bottom)
- `.mt-0` até `.mt-5` (margin-top)

### Display e Flex
- `.d-none`, `.d-block`, `.d-flex`, `.d-grid`
- `.flex-center`, `.flex-between`, `.flex-column`

### Responsivo
- `.d-md-none`, `.d-sm-none`

## 🚀 Como Usar

### 1. Importação nos Arquivos PHP

```php
<head>
    <!-- Common CSS (sempre primeiro) -->
    <link rel="stylesheet" href="assets/css/common.css">
    <!-- CSS específico da página -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
```

### 2. Ordem de Importação Recomendada

1. `common.css` - Base e utilitários
2. CSS específico da página
3. CSS customizações locais (se necessário)

## 📋 Checklist de Implementação

- [x] Extrair CSS inline das páginas PHP
- [x] Criar estrutura de arquivos CSS organizados  
- [x] Implementar variáveis CSS para consistência
- [x] Adicionar seção da equipe com 5 membros
- [x] Garantir responsividade em todos os breakpoints
- [x] Documentar estrutura e uso
- [ ] Atualizar referências nas páginas PHP
- [ ] Testar compatibilidade entre navegadores

## 🔄 Próximos Passos

1. Substituir CSS inline pelas referências aos arquivos
2. Adicionar imagens da equipe na pasta `assets/images/`
3. Implementar lazy loading para imagens
4. Adicionar temas alternativos (escuro/claro)
5. Otimizar CSS para performance

## 📞 Contato da Equipe

Para dúvidas sobre a implementação CSS, consulte a documentação técnica ou entre em contato com a equipe de desenvolvimento.

---

**Versão**: 2.0  
**Data**: 29/09/2025  
**Desenvolvido por**: Equipe FinSecure