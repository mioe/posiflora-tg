import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface TelegramConnectRequest {
  botToken: string;
  chatId: string;
  enabled: boolean;
}

export interface TelegramConnectResponse {
  id: string;
  shopId: string;
  chatId: string;
  enabled: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface TelegramStatusResponse {
  enabled: boolean;
  chatId: string | null;
  lastSentAt: string | null;
  sentCount: number;
  failedCount: number;
}

export interface Shop {
  id: string;
  name: string;
}

@Injectable({ providedIn: 'root' })
export class TelegramApiService {
  private http = inject(HttpClient);
  private baseUrl = '';

  getShops(): Observable<Shop[]> {
    return this.http.get<Shop[]>(`${this.baseUrl}/shops`);
  }

  connect(shopId: string, payload: TelegramConnectRequest): Observable<TelegramConnectResponse> {
    return this.http.post<TelegramConnectResponse>(
      `${this.baseUrl}/shops/${shopId}/telegram/connect`,
      payload,
    );
  }

  getStatus(shopId: string): Observable<TelegramStatusResponse> {
    return this.http.get<TelegramStatusResponse>(
      `${this.baseUrl}/shops/${shopId}/telegram/status`,
    );
  }
}
