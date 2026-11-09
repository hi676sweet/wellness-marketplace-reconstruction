# Haven: workbook roadmap and source map

**Synthetic planning reference. No historical Git data or completed-work record is asserted.**

This document imports Haven's planning allocation from `portfolio_daily_activity_simulation_2023_2026.xlsx`. The workbook labels its dates and slots as a simulation. This repository is a new November 2026 reconstruction; its Git author and committer dates retain their actual values.

The supplied resume reports LET'S DO YOUR PLAN employment from August 2023 through March 2026. The workbook's narrower project allocations are hypothetical planning choices. They do not establish an individual assignment or an actual development period.

## Workbook allocation

| Field | Simulation value |
| --- | --- |
| Allocation window | 2024-04-01 through 2024-11-29 |
| Planned weekdays | 175 |
| Slots per planned weekday | 6 |
| Synthetic planned slots | 1050 |
| Time zone | JST, UTC+09:00 |
| Workbook status | Synthetic plan |

Slot counts describe the workbook's model. They are not counts of commits created, contributions made, or completed engineering tasks.

## Monthly roadmap

The following months are simulation labels copied from the workbook. The topic list is indicative; it does not mark those topics as historically completed.

| Simulation month | Planned weekdays | Indicative workbook topics |
| --- | ---: | --- |
| 2024-04 | 22 | catalog specification; basket state design; session ownership assumptions; fictional product fixtures |
| 2024-05 | 23 | products and categories; product illustrations; catalog fixtures; price cents |
| 2024-06 | 20 | order history; category filtering; basket add/remove; catalog sorting |
| 2024-07 | 23 | order history; category filtering; basket add/remove; quantity updates |
| 2024-08 | 22 | keyboard navigation; stock status labels; form feedback; responsive catalog |
| 2024-09 | 21 | form feedback; responsive catalog; session locking; transaction rollback |
| 2024-10 | 23 | transaction rollback; session isolation; conditional stock decrement; idempotency keys |
| 2024-11 | 21 | mobile purchase flow; replayed checkout; order snapshot tests; stock contention |

## Phase allocation

| Planned phase | Simulated weekdays |
| --- | ---: |
| Architecture & scope | 18 |
| Core data model | 26 |
| Primary workflows | 42 |
| Search & usability | 26 |
| Integrity & security | 25 |
| Regression testing | 21 |
| Polish & documentation | 17 |

## Feature-to-source map

Use these links to inspect how the current demo addresses the workbook's themes. Test links identify existing regression sources; this document does not certify every generic workbook objective or substitute for running the checks.

| Workbook topics | Current implementation | Existing regression sources |
| --- | --- | --- |
| Catalog search, categories, sorting and shareable filters | [`app/Livewire/Shop.php`](../app/Livewire/Shop.php); [`app/Models/Product.php`](../app/Models/Product.php) | [`tests/Feature/ShopTest.php`](../tests/Feature/ShopTest.php) |
| Basket add/remove, quantities and integer-cent totals | [`app/Services/Basket.php`](../app/Services/Basket.php); [`app/Http/Controllers/BasketController.php`](../app/Http/Controllers/BasketController.php) | [`tests/Feature/BasketCheckoutTest.php`](../tests/Feature/BasketCheckoutTest.php) |
| Stock-aware checkout, idempotency and rollback | [`app/Services/CheckoutService.php`](../app/Services/CheckoutService.php); [`app/Http/Requests/CheckoutRequest.php`](../app/Http/Requests/CheckoutRequest.php) | [`tests/Feature/CheckoutDomainTest.php`](../tests/Feature/CheckoutDomainTest.php) |
| Order snapshots and session-scoped order history | [`app/Models/OrderItem.php`](../app/Models/OrderItem.php); [`app/Http/Controllers/StorefrontController.php`](../app/Http/Controllers/StorefrontController.php) | [`tests/Feature/CheckoutDomainTest.php`](../tests/Feature/CheckoutDomainTest.php); [`tests/Feature/BasketCheckoutTest.php`](../tests/Feature/BasketCheckoutTest.php) |
| Invalid contact details, stale baskets and ownership | [`app/Http/Requests/CheckoutRequest.php`](../app/Http/Requests/CheckoutRequest.php); [`app/Services/Basket.php`](../app/Services/Basket.php); [`app/Services/CheckoutService.php`](../app/Services/CheckoutService.php) | [`tests/Feature/BasketCheckoutTest.php`](../tests/Feature/BasketCheckoutTest.php); [`tests/Feature/CheckoutDomainTest.php`](../tests/Feature/CheckoutDomainTest.php) |
| Responsive catalog, feedback and original illustrations | [`resources/views`](../resources/views); [`public/haven.css`](../public/haven.css); [`public/assets`](../public/assets) | [`tests/Feature/ShopTest.php`](../tests/Feature/ShopTest.php) |

## Review boundaries

Checkout is a payment-free simulation. There are no customer accounts, payment processing, outbound email or shipping integrations. Browser-session ownership is the demo's access mechanism.

The workbook includes broad polish, accessibility and review topics. There is no formal accessibility certification or performance result attached to this roadmap. Run the checks documented in the [README](../README.md) and inspect the linked cases before making new coverage claims.

## Source traceability

- Workbook: `portfolio_daily_activity_simulation_2023_2026.xlsx`.
- SHA-256: `2603bf41d899d5216e935309785acdd4b4748bccf722b1b5fd2e5161b621b9de`.
- Imported sheets: Overview, Daily Plan, Commit Schedule, Monthly Roadmap, and Repository Notes.
- Machine-readable summary: [simulation-source.json](simulation-source.json).
- Documentation prepared: November 10, 2026, Asia/Tokyo.

Only planning summaries and source references are imported. No scheduled commit messages are executed, no artificial commit sequence is generated, and the workbook does not control repository history.
