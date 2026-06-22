import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-admin-shell-page',
  imports: [MatIconModule, RouterLink, RouterLinkActive, RouterOutlet],
  template: `
    <main class="page space-y-6">
      <section class="hero-panel">
        <p class="section-eyebrow !text-white/80 before:!bg-white">Super Admin</p>
        <h1 class="m-0 max-w-3xl text-4xl font-black leading-none tracking-tight sm:text-6xl">
          Administracion SaaS
        </h1>
        <p class="mt-5 max-w-2xl leading-7 text-white/76">
          Gestiona municipios, branding, dominios, usuarios y metricas globales.
        </p>
      </section>

      <nav class="admin-tabs">
        @for (item of tabs; track item.path) {
          <a [routerLink]="item.path" routerLinkActive="active" [routerLinkActiveOptions]="{ exact: true }">
            <mat-icon>{{ item.icon }}</mat-icon>
            <span>{{ item.label }}</span>
          </a>
        }
      </nav>

      <router-outlet />
    </main>
  `,
  styles: [
    `
      .admin-tabs {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 28px;
        padding: 10px;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: var(--shadow-soft);
      }

      .admin-tabs a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 20px;
        padding: 12px 16px;
        color: var(--color-muted);
        font-weight: 900;
      }

      .admin-tabs a.active,
      .admin-tabs a:hover {
        background: var(--color-primary);
        color: #fff;
      }
    `,
  ],
})
export class AdminShellPage {
  protected readonly tabs = [
    { path: ['/admin', 'metricas'], label: 'Metricas', icon: 'analytics' },
    { path: ['/admin', 'municipios'], label: 'Municipios', icon: 'account_balance' },
    { path: ['/admin', 'usuarios'], label: 'Usuarios', icon: 'group' },
  ];
}
