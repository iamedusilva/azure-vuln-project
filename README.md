# 🛡️ Azure Vuln Project  

> **⚠️ AVISO:** Este projeto é propositalmente vulnerável e serve **apenas para fins educacionais** em Segurança da Informação.  
> Demonstra como identificar, explorar e proteger aplicações hospedadas no Azure usando WAF.  
> **NÃO USE EM PRODUÇÃO.**

---

## 📋 Pré-requisitos
- **PHP 8.x** + XAMPP ou servidor similar  
- **Azure SQL Database** configurado  
- Extensões PHP: PDO + SQLSRV  

---

## 🛠️ Instalação Rápida
```bash
git clone [URL_DO_REPOSITORIO]
cd Azure-Web-App---Vuln

# Configure o banco
mysql -u root -p < database/create_database.sql

# Ajuste config/database.php com suas credenciais

# Inicie Apache e MySQL no XAMPP e acesse:
http://localhost/Azure-Web-App---Vuln/app/public
```

---

## 🔑 Usuários de Teste
| Usuário  | Senha    | Papel   |
|---------|---------|--------|
| admin   | admin   | admin |
| root    | 123456  | admin |
| user1   | password| user  |
| guest   | guest   | guest |
| test    | test123 | user  |
| demo    | demo    | user  |
| manager | manager123 | user |

---

## 🧪 Vulnerabilidades Educacionais
- **SQL Injection** – Login e consultas  
- **XSS (Stored & Reflected)** – Sistema de comentários  
- **CSRF** – Formulários críticos  
- **Exposição de Dados** – Senhas e configs em texto plano  
- **Logs Inseguros** – Armazenamento de senhas tentadas  
- **Configurações Inseguras** – Permissões excessivas  

---

## 📁 Estrutura Básica
```
Azure-Web-App---Vuln/
├── app/public/        # Páginas (login, register, dashboard)
├── config/            # Conexão com DB
├── database/          # Scripts SQL
└── assets/            # CSS, imagens, JS
```

---

## 🎨 Funcionalidades
- Login/registro com armazenamento inseguro  
- Dashboard com estatísticas e comentários  
- Sistema responsivo (mobile-first)  
- Modais e animações em CSS  
- Logs de tentativas de login  

---

## 📚 Objetivo Educacional
- Estudar ataques (SQLi, XSS, CSRF)  
- Analisar impactos (bypass login, vazamento de dados)  
- Aprender mitigação com WAF e boas práticas  

---

## 🔧 Troubleshooting
- Verifique se Apache/MySQL estão rodando no XAMPP  
- Confirme credenciais em `config/database.php`  
- Revise logs do PHP/Apache para erros  
- Reimporte o script SQL se necessário  

---

## 📄 Licença & Aviso
- ✅ **Uso permitido:** Ensino, pesquisa, laboratórios controlados  
- ❌ **Proibido:** Produção ou ambientes reais  
- **Disclaimer:** Os autores não se responsabilizam por uso indevido.  
- Esta aplicação é **vulnerável por design** – use com responsabilidade.  

---

**📧 Contato:**
**📅 Última Atualização:** Dez/2024  
> *"A melhor defesa é conhecer o ataque"*  
