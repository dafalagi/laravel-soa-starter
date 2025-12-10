# 🏗️ Laravel SOA Starter

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.3+-blue?style=flat-square&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/Architecture-Modular%20SOA-green?style=flat-square" alt="Architecture">
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=flat-square" alt="License">
</p>

A **Service-Oriented Architecture (SOA)** starter template built on Laravel 12, featuring a **modular monolith** design pattern with comprehensive service layer architecture, automated discovery, and enterprise-grade patterns.

## 🎯 **Project Vision**

This starter template provides a solid foundation for building scalable, maintainable Laravel applications using SOA principles and modular architecture. It emphasizes clean separation of concerns, testability, and developer productivity through intelligent code generation and consistent patterns.

## ✨ **Key Features**

### 🏛️ **Modular Architecture**
- **Modular Monolith Design** - Self-contained modules with clear boundaries
- **Service-Oriented Architecture** - Business logic encapsulated in dedicated services  
- **Client-Type Separation** - Routes, controllers, requests, and resources organized by client (Admin/Web/Mobile)
- **Code Generation Commands** - Automated scaffolding with `make:controller`, `make:request`, `make:resource`, `make:dto`
- **Automatic Module Discovery** - Routes, migrations, and services auto-registered

### 🔧 **Advanced Service Layer**
- **BaseService Pattern** - Consistent transaction handling and error management
- **Single Responsibility Services** - Each service handles one specific action
- **Comprehensive Validation** - Request validation moved to service layer
- **Standardized Response Format** - Uniform API responses across all endpoints
- **Client-Optimized Responses** - Tailored data structures for Admin, Web, and Mobile clients

### 🧪 **Testing & Quality**
- **Modular Test Structure** - Tests organized per module
- **Comprehensive Test Coverage** - Unit and feature tests included

### 📱 **Client-Type Architecture**
- **Multi-Client Support** - Separate API endpoints for Admin, Web, and Mobile clients
- **Optimized Performance** - Client-specific controllers reduce unnecessary processing
- **Targeted Responses** - Resources tailored for each client's data requirements
- **Separation of Concerns** - Admin features isolated from public web/mobile interfaces
- **Maintainability** - Easy to modify client-specific logic without affecting others
- **Security** - Clear boundaries between administrative and public functionality

### 🗄️ **Database Architecture**  
- **Module-Specific Databases** - Each module manages its own migrations, factories, seeders
- **Migration Organization** - Clear separation of database concerns per module

### 🎨 **Developer Experience**
- **Comprehensive Code Generation** - Controllers, requests, resources, and DTOs with consistent patterns
- **Rich Documentation** - Comprehensive guides and architectural documentation  
- **Naming Conventions Consistency** - Snake_case variables, proper suffixes, and organized namespacing
- **Clean Architecture** - Clear separation between controllers, services, models, and client types

## 🏗️ **Architecture Overview**

```mermaid
graph TB
    %% Client Applications Layer
    subgraph Clients ["🌐 Client Applications"]
        Admin["👨‍💼 Admin Dashboard"]
        Web["🌐 Web Frontend"]
        Mobile["📱 Mobile App"]
    end

    %% API Gateway Layer
    subgraph Gateway ["🛣️ API Gateway"]
        AdminRoutes["/api/v*/admin/*"]
        WebRoutes["/api/v*/web/*"]
        MobileRoutes["/api/v*/mobile/*"]
    end

    %% Application Layer
    subgraph AppLayer ["🎮 Application Layer"]
        AdminControllers["Admin Controllers"]
        WebControllers["Web Controllers"] 
        MobileControllers["Mobile Controllers"]
        
        AdminRequests["Admin Requests"]
        WebRequests["Web Requests"]
        MobileRequests["Mobile Requests"]
        
        AdminResources["Admin Resources"]
        WebResources["Web Resources"]
        MobileResources["Mobile Resources"]
    end

    %% Service Layer (SOA Core)
    subgraph ServiceLayer ["🔧 Service Layer (SOA)"]
        subgraph AuthModule ["Auth Module"]
            AuthServices["🔐 Auth Services<br/>• LoginService<br/>• TokenService<br/>• RegisterService"]
            AuthModels["📊 Models & DTOs"]
        end
        
        subgraph ProductModule ["Product Module"]
            ProductServices["📦 Product Services<br/>• CreateProductService<br/>• UpdateProductService<br/>• DeleteProductService"]
            ProductModels["📊 Models & DTOs"]
        end
        
        subgraph OrderModule ["Order Module"]
            OrderServices["🛒 Order Services<br/>• CreateOrderService<br/>• PaymentService<br/>• StatusService"]
            OrderModels["📊 Models & DTOs"]
        end
    end

    %% Database Layer
    subgraph DatabaseLayer ["💾 Database Layer"]
        AuthDB[("🔐 Auth<br/>Database")]
        ProductDB[("📦 Product<br/>Database")]
        OrderDB[("🛒 Order<br/>Database")]
    end

    %% Client to Gateway connections
    Admin --> AdminRoutes
    Web --> WebRoutes
    Mobile --> MobileRoutes

    %% Gateway to Controllers
    AdminRoutes --> AdminControllers
    WebRoutes --> WebControllers
    MobileRoutes --> MobileControllers

    %% Controllers to HTTP Layer
    AdminControllers --> AdminRequests
    AdminControllers --> AdminResources
    WebControllers --> WebRequests
    WebControllers --> WebResources
    MobileControllers --> MobileRequests
    MobileControllers --> MobileResources

    %% All Controllers to Services
    AdminControllers --> AuthServices
    AdminControllers --> ProductServices
    AdminControllers --> OrderServices
    
    WebControllers --> AuthServices
    WebControllers --> ProductServices
    WebControllers --> OrderServices
    
    MobileControllers --> AuthServices
    MobileControllers --> ProductServices
    MobileControllers --> OrderServices

    %% Services to Models
    AuthServices --> AuthModels
    ProductServices --> ProductModels
    OrderServices --> OrderModels

    %% Models to Databases
    AuthModels --> AuthDB
    ProductModels --> ProductDB
    OrderModels --> OrderDB

    %% Inter-module communication (optional)
    AuthServices -.->|"Cross-module<br/>communication"| ProductServices
    ProductServices -.-> OrderServices

    %% Styling
    classDef clientStyle fill:#e1f5fe,stroke:#01579b,stroke-width:2px,color:#000000
    classDef gatewayStyle fill:#f3e5f5,stroke:#4a148c,stroke-width:2px,color:#000000
    classDef appStyle fill:#e8f5e8,stroke:#1b5e20,stroke-width:2px,color:#000000
    classDef serviceStyle fill:#fff3e0,stroke:#e65100,stroke-width:2px,color:#000000
    classDef dbStyle fill:#fce4ec,stroke:#880e4f,stroke-width:2px,color:#000000

    class Admin,Web,Mobile clientStyle
    class AdminRoutes,WebRoutes,MobileRoutes gatewayStyle
    class AdminControllers,WebControllers,MobileControllers,AdminRequests,WebRequests,MobileRequests,AdminResources,WebResources,MobileResources appStyle
    class AuthServices,ProductServices,OrderServices,AuthModels,ProductModels,OrderModels serviceStyle
    class AuthDB,ProductDB,OrderDB dbStyle
```

## 📁 **Project Structure**

```
├── app/
│   ├── Console/Commands/
│   │   ├── Database/                      # Database related commands
│   │   │   └── MigrateFresh.php
│   │   └── Make/                          # Code generation commands
│   │       ├── MakeControllerCommand.php
│   │       ├── MakeRequestCommand.php
│   │       ├── MakeResourceCommand.php
│   │       └── MakeDtoCommand.php
│   ├── Services/
│   │   └── BaseService.php                # Base service with common functionality
│   ├── Http/Traits/
│   │   └── ApiResponse.php                # Standardized API responses
│   └── Traits/
│       └── HasModularFactory.php          # Automatic factory discovery
├── modules/
│   └── Auth/                              # Example Auth module
│       ├── DTOs/                          # Data Transfer Objects
│       │   ├── User/Requests/             # Request DTOs
│       │   └── User/Responses/            # Response DTOs
│       ├── Http/
│       │   ├── Controllers/Api/           # Client-specific controllers
│       │   │   ├── Admin/                 # Admin controllers
│       │   │   ├── Web/                   # Web controllers
│       │   │   └── Mobile/                # Mobile controllers
│       │   ├── Requests/Api/              # Client-specific form requests
│       │   │   ├── Admin/User/            # Admin user requests
│       │   │   ├── Web/User/              # Web user requests
│       │   │   └── Mobile/User/           # Mobile user requests
│       │   └── Resources/Api/             # Client-specific JSON resources
│       │       ├── Admin/User/            # Admin user resources
│       │       ├── Web/User/              # Web user resources
│       │       └── Mobile/User/           # Mobile user resources
│       ├── Services/                      # Business logic layer
│       │   ├── Auth/                      # Authentication services
│       │   └── User/                      # User management services
│       ├── Models/                        # Eloquent models
│       ├── Database/                      # Module-specific database files
│       │   ├── Migrations/
│       │   ├── Factories/
│       │   └── Seeders/
│       └── Tests/                         # Module tests
├── docs/
│   ├── MODULAR_DATABASE.md              # Database architecture guide
└── README.md
```

## 🚀 **Getting Started**

### Prerequisites
- PHP 8.3+
- Laravel 12.x
- PostgreSQL (preferably)/MySQL/SQLite

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/laravel-soa-starter.git
   cd laravel-soa-starter
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database and Passport setup**
   ```bash
   php artisan db:migrate-fresh
   ```

5. **Run the application**
   ```bash
   php artisan serve
   ```

### Creating Your First Module

Generate a new module with all necessary files:

```bash
php artisan make:module Product
```

This creates a complete module structure consisted of:
- API Controllers
- Service layer
- DTOs for data transfer
- Models
- Database migrations, factories, seeders
- Feature and unit tests
- API routes
- Localization

### Generating Components

The complete list of available component generator can be checked at [make commands](app/Console/Commands/Make/)

## 🏗️ **Architecture Patterns**

### Service Layer Architecture
```php
// Each service extends BaseService for consistency
class CreateProductService extends BaseService implements // Contract/Interface
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
    
    protected function process(mixed $dto): void
    {
        // Business logic here
        $product = Product::create($dto);
        $this->results['data'] = ProductResponseDTO::fromModel($product);
    }
}
```

### Client-Specific Architecture
```php
// Admin Controller - Full access with detailed responses
class AdminProductController extends ApiController
{
    public function index(): JsonResponse
    {
        $products = Product::with(['category', 'createdBy'])->get();
        return $this->response([
            'products' => AdminProductListResource::collection($products)
        ]);
    }
}

// Web Controller - Public-safe data only
class WebProductController extends ApiController
{
    public function index(): JsonResponse
    {
        $products = Product::active()->get();
        return $this->response([
            'products' => WebProductCardResource::collection($products)
        ]);
    }
}

// Mobile Controller - Optimized for bandwidth
class MobileProductController extends ApiController
{
    public function index(): JsonResponse
    {
        $products = Product::active()->select(['id', 'name', 'price'])->get();
        return $this->response([
            'products' => MobileProductResource::collection($products)
        ]);
    }
}
```

## 📊 **Current Status**

- ✅ **Core Architecture** - Modular SOA foundation complete
- ✅ **Client-Type Separation** - Admin/Web/Mobile architecture implemented
- ✅ **Auth Module** - Complete authentication system with Passport
- ✅ **Service Layer** - BaseService pattern with comprehensive error handling  
- ✅ **Code Generation Suite** - Commands for controllers, requests, resources, DTOs
- ✅ **Factory Discovery** - Automatic model factory resolution
- ✅ **Testing Framework** - Modular test structure with comprehensive coverage
- ✅ **Modular Localization** - Translation system integrated per module
- ✅ **CI/CD Pipeline** - GitHub Actions for automated testing and deployment
- ✅ **API Versioning** - Support for multiple API versions

## 🗺️ **Roadmap & Future Plans**

### Phase 1: Core Enhancements
- [ ] **API Rate Limiting** - Comprehensive rate limiting per module/endpoint
- [ ] **Caching Layer** - Redis-based caching with cache tags per module
- [ ] **Event System** - Module-to-module communication via events
- [ ] **Permission System** - Role-based access control (RBAC)

### Phase 2: Advanced Features
- [ ] **Queue System** - Background job processing per module
- [ ] **File Management** - File upload/storage service with cloud support
- [ ] **Notification System** - Multi-channel notifications (email, SMS, push)

### Phase 3: DevOps & Monitoring
- [ ] **Docker Support** - Complete containerization setup
- [ ] **Monitoring & Logging** - Comprehensive application monitoring
- [ ] **Performance Optimization** - Database query optimization and caching strategies

### Phase 4: Enterprise Features
- [ ] **Multi-tenancy** - SaaS-ready tenant isolation
- [ ] **Microservice Migration Path** - Tools to split modules into microservices
- [ ] **API Gateway** - Centralized API management and routing
- [ ] **Distributed Tracing** - Request tracing across modules

## 🤝 **Contributing**

We welcome contributions! Please see our [contributing guidelines](CONTRIBUTING.md) for details.

### Development Setup
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 🧪 **Testing**

Run the test suite:
```bash
# Run all tests
vendor/bin/phpunit

# Run specific module tests
vendor/bin/phpunit modules/Auth/Tests/

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

## 📚 **Documentation**

Comprehensive documentation is available in the [`docs/`](docs/) folder, including:
- **Architecture Guides** - Modular database and localization patterns
- **Command Documentation** - Detailed guides for all code generation commands
- **Best Practices** - Development patterns and conventions

Key documentation:
- [Command Reference](docs/commands/) - All available artisan commands
- [Modular Database](docs/MODULAR_DATABASE.md) - Database architecture patterns

## 🙏 **Acknowledgments**

Special thanks to the following developers for their inspiration, suggestions, and contributions to this project:

- **[@lazuardy347](https://github.com/lazuardy347)** - For architectural insights and design pattern suggestions
- **[@praneshaw](https://github.com/praneshaw)** - For modular design pattern inspiration and feedback
- **[@dimasaprasetyo](https://github.com/dimasaprasetyo)** - For helpful libraries, packages, and tools recommendations.

Their expertise and guidance have been invaluable in shaping this project's architecture and implementation.

## 📄 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 💡 **Support**

- 📧 **Email**: [dafarizky34@gmail.com](mailto:dafarizky34@gmail.com)
- 🐛 **Issues**: [GitHub Issues](https://github.com/dafalagi/laravel-soa-starter/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/dafalagi/laravel-soa-starter/discussions)

---

<p align="center">
  <strong>Built with ❤️ using Laravel and modern PHP practices</strong>
</p>
