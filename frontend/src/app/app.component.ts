import { Component, computed, inject } from '@angular/core';
import { RouterLink, RouterOutlet } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatToolbarModule } from '@angular/material/toolbar';
import { TenantService } from './core/services/tenant.service';

@Component({
  selector: 'app-root',
  imports: [MatButtonModule, MatToolbarModule, RouterLink, RouterOutlet],
  template: `
    <mat-toolbar
      class="app-toolbar"
      [style.--tenant-primary]="primaryColor()"
      [style.--tenant-secondary]="secondaryColor()"
    >
      <div class="toolbar-shell">
        <a routerLink="/" class="brand">
          <span class="brand-mark">MX</span>
          <span class="brand-copy">
            <strong>{{ tenantName() }}</strong>
            <small>Plataforma segura de pagos municipales</small>
          </span>
        </a>

        <nav class="nav-actions" aria-label="Navegacion principal">
          <a mat-button routerLink="/recibos">Recibos</a>
          <a mat-button routerLink="/admin">Admin</a>
          <a mat-flat-button class="treasury-cta" routerLink="/login">Tesoreria</a>
        </nav>
      </div>
    </mat-toolbar>

    <router-outlet />
  `,
  styles: [
    `
      .app-toolbar {
        --tenant-primary: var(--color-primary);
        --tenant-secondary: var(--color-secondary);
        position: sticky;
        top: 0;
        z-index: 40;
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(15, 76, 129, 0.88);
        color: #fff;
        min-height: 78px;
        padding: 0;
        backdrop-filter: blur(18px);
      }

      .toolbar-shell {
        display: flex;
        width: min(1180px, calc(100% - 24px));
        align-items: center;
        justify-content: space-between;
        margin: 0 auto;
        gap: 14px;
      }

      .brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
      }

      .brand-mark {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 16px;
        background: linear-gradient(135deg, var(--tenant-secondary), rgba(255, 255, 255, 0.26));
        box-shadow: 0 16px 34px rgba(0, 0, 0, 0.16);
        font-weight: 800;
      }

      .brand-copy {
        display: grid;
        min-width: 0;
      }

      .brand-copy strong {
        overflow: hidden;
        max-width: 42vw;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .brand-copy small {
        display: block;
        opacity: 0.82;
        font-size: 0.76rem;
      }

      .nav-actions {
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .treasury-cta {
        border-radius: 999px;
        background: #fff !important;
        color: var(--tenant-primary) !important;
        font-weight: 800;
      }

      @media (max-width: 640px) {
        .brand-copy small {
          display: none;
        }

        .nav-actions a:first-child {
          display: none;
        }
      }
    `,
  ],
})
export class AppComponent {
  private readonly tenantService = inject(TenantService);
  protected readonly tenantName = computed(() => this.tenantService.tenant()?.name ?? 'Pagos Municipales');
  protected readonly primaryColor = computed(() => this.tenantService.tenant()?.primary_color ?? '#0F4C81');
  protected readonly secondaryColor = computed(() => this.tenantService.tenant()?.secondary_color ?? '#B08D57');

  constructor() {
    this.tenantService.loadTenant().subscribe();
  }
}
