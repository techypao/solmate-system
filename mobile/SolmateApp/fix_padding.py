import re

screens = [
    'screens/HomeScreen.tsx',
    'screens/TrackingHubScreen.tsx',
    'screens/InspectionHubScreen.tsx',
    'screens/ServicesHubScreen.tsx',
    'screens/CustomerSettingsScreen.tsx',
    'screens/InspectionRequestListScreen.tsx',
    'screens/ServiceRequestListScreen.tsx',
    'screens/QuotationListScreen.tsx',
    'screens/MyTestimoniesScreen.tsx',
    'screens/CustomerNotificationsScreen.tsx',
    'screens/InspectionRequestScreen.tsx',
    'screens/ServiceRequestScreen.tsx',
    'screens/InstallationRequestScreen.tsx',
    'screens/QuotationScreen.tsx',
    'screens/QuotationDetailScreen.tsx',
    'screens/FinalQuotationViewScreen.tsx',
]

# Replace paddingBottom in the scroll: style block (single-line)
# Pattern: scroll: { ... paddingBottom: N, ... }
REPLACE_PB = re.compile(r'(scroll:\s*\{[^}]*?)paddingBottom:\s*\d+([^}]*\})')
ADD_PB = re.compile(r'(scroll:\s*\{)([^}]*?)(\})')

for path in screens:
    with open(path, 'r') as f:
        c = f.read()

    original = c

    # Replace existing paddingBottom in scroll block
    def replace_pb(m):
        return m.group(1) + 'paddingBottom: 90' + m.group(2)

    new_c = REPLACE_PB.sub(replace_pb, c)

    # If no paddingBottom was found in scroll block, add it
    if new_c == c:
        def add_pb(m):
            body = m.group(2)
            if 'paddingBottom' in body:
                return m.group(0)
            return m.group(1) + body.rstrip() + ' paddingBottom: 90,' + m.group(3)
        new_c = ADD_PB.sub(add_pb, c)

    if new_c != original:
        with open(path, 'w') as f:
            f.write(new_c)
        print('ok ' + path)
    else:
        print('-- (no scroll: style found) ' + path)

print('Done.')
