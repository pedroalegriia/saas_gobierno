import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { TenantService } from '../services/tenant.service';

export const tenantInterceptor: HttpInterceptorFn = (request, next) => {
  const tenant = inject(TenantService).tenant();

  if (!tenant) {
    return next(request);
  }

  return next(
    request.clone({
      setHeaders: {
        'X-Tenant-Slug': tenant.slug,
      },
    }),
  );
};
