import { Component, computed, inject } from '@angular/core';
import { RouterLink, RouterOutlet } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatToolbarModule } from '@angular/material/toolbar';
import { TenantService } from './core/services/tenant.service';

@Component({
  selector: 'app-root',
  imports: [MatButtonModule, MatToolbarModule, RouterLink, RouterOutlet],
  template: `
    <mat-toolbar class="app-toolbar" [style.--tenant-primary]="primaryColor()" [style.--tenant-secondary]="secondaryColor()">
      <a routerLink="/" class="brand">
        <span class="brand-mark">MX</span>
        <span>
          <strong>{{ tenantName() }}</strong>
          <small>Pagos en linea</small>
        </span>
      </a>
      <span class="spacer"></span>
      <a mat-button routerLink="/recibos">Recibos</a>
      <a mat-flat-button routerLink="/login">Tesoreria</a>
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
        z-index: 10;
        background: var(--tenant-primary);
        color: #fff;
        min-height: 72px;
        padding: 0 16px;
      }

      .brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
      }

      .brand-mark {
        display: grid;
        width: 44px;
        height: 44px;
        place-items: center;
        border-radius: 14px;
        background: var(--tenant-secondary);
        font-weight: 800;
      }

      .brand small {
        display: block;
        opacity: 0.82;
        font-size: 0.76rem;
      }

      .spacer {
        flex: 1 1 auto;
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
