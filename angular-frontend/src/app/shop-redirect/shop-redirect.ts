import { Component, OnInit, inject } from '@angular/core';
import { Router } from '@angular/router';
import { TelegramApiService } from '../telegram/telegram-api.service';

@Component({
  selector: 'app-shop-redirect',
  standalone: true,
  template: `<p style="padding:2rem;font-family:sans-serif">Загрузка магазина...</p>`,
})
export class ShopRedirectComponent implements OnInit {
  private api = inject(TelegramApiService);
  private router = inject(Router);

  ngOnInit(): void {
    this.api.getShops().subscribe({
      next: (shops) => {
        if (shops.length > 0) {
          this.router.navigate(['/shops', shops[0].id, 'growth', 'telegram']);
        } else {
          document.body.innerHTML = '<p style="padding:2rem;font-family:sans-serif">Нет магазинов. Запустите fixtures: <code>php bin/console doctrine:fixtures:load</code></p>';
        }
      },
      error: () => {
        document.body.innerHTML = '<p style="padding:2rem;font-family:sans-serif;color:red">Не удалось подключиться к backend (http://localhost:8080). Убедитесь, что сервер запущен.</p>';
      },
    });
  }
}
