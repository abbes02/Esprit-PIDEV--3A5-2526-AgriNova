# PDF Export Setup (Agrinova Plantes)

The PDF export button is implemented. To enable:

1. Install KnpSnappy:
```
cd parcelle
composer require knp/snappy
```

2. Install wkhtmltopdf (Windows):
   - Download from https://wkhtmltopdf.org/downloads.html (0.12.6 or later)
   - Install to `C:\\Program Files\\wkhtmltopdf\\`
   - Verify path in `config/packages/knp_snappy.yaml`

3. Clear cache:
```
php bin/console cache:clear
```

4. Start server:
```
symfony server:start
```

5. Test:
- Go to http://localhost:8000/plante/
- Click "📄 Export PDF"
- Download `agrinova_plantes_YYYY-MM-DD.pdf`
- Open PDF: Verify "agrinova" title, date, plants table.

Done! See TODO.md for code changes.
