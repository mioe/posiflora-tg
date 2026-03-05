import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { TelegramApiService, TelegramStatusResponse } from './telegram-api.service';

@Component({
  selector: 'app-telegram-page',
  standalone: true,
  imports: [CommonModule, FormsModule, DatePipe],
  templateUrl: './telegram-page.html',
  styleUrl: './telegram-page.css',
})
export class TelegramPageComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private api = inject(TelegramApiService);

  shopId = '';
  botToken = '';
  chatId = '';
  enabled = true;

  saving = signal(false);
  saveSuccess = signal(false);
  saveError = signal('');

  status = signal<TelegramStatusResponse | null>(null);
  statusLoading = signal(false);
  statusError = signal('');

  ngOnInit(): void {
    this.shopId = this.route.snapshot.paramMap.get('shopId') ?? '';
    this.loadStatus();
  }

  save(): void {
    if (!this.botToken.trim() || !this.chatId.trim()) {
      this.saveError.set('Bot Token и Chat ID обязательны');
      return;
    }

    this.saving.set(true);
    this.saveError.set('');
    this.saveSuccess.set(false);

    this.api.connect(this.shopId, { botToken: this.botToken, chatId: this.chatId, enabled: this.enabled }).subscribe({
      next: () => {
        this.saveSuccess.set(true);
        this.saving.set(false);
        this.loadStatus();
      },
      error: (err) => {
        this.saveError.set(err.error?.error ?? 'Ошибка при сохранении');
        this.saving.set(false);
      },
    });
  }

  loadStatus(): void {
    this.statusLoading.set(true);
    this.statusError.set('');

    this.api.getStatus(this.shopId).subscribe({
      next: (s) => {
        this.status.set(s);
        this.enabled = s.enabled;
        this.statusLoading.set(false);
      },
      error: () => {
        this.statusError.set('Не удалось загрузить статус');
        this.statusLoading.set(false);
      },
    });
  }
}
