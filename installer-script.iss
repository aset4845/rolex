; Скрипт Inno Setup для создания установщика Rolex Shop
; Требуется Inno Setup 6.0 или выше
; Скачать: https://jrsoftware.org/isdl.php

#define MyAppName "Rolex Shop"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "Your Company Name"
#define MyAppURL "https://yourwebsite.com"
#define MyAppExeName "RolexShop.exe"

[Setup]
; Основные настройки приложения
AppId={{YOUR-UNIQUE-APP-ID-HERE}}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName={autopf}\{#MyAppName}
DefaultGroupName={#MyAppName}
AllowNoIcons=yes
LicenseFile=LICENSE.txt
; Убедитесь что у вас есть иконка
; SetupIconFile=icon.ico
OutputDir=installer-output
OutputBaseFilename=RolexShop-Setup-{#MyAppVersion}
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
PrivilegesRequired=admin
ArchitecturesInstallIn64BitMode=x64

[Languages]
Name: "russian"; MessagesFile: "compiler:Languages\Russian.isl"
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked
Name: "quicklaunchicon"; Description: "{cm:CreateQuickLaunchIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked; OnlyBelowVersion: 0,6.1

[Files]
; Замените C:\RolexShop-Portable на путь к вашей собранной папке
Source: "C:\RolexShop-Portable\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs
; NOTE: Don't use "Flags: ignoreversion" on any shared system files

[Icons]
Name: "{group}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"
Name: "{group}\{cm:UninstallProgram,{#MyAppName}}"; Filename: "{uninstallexe}"
Name: "{autodesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon
Name: "{userappdata}\Microsoft\Internet Explorer\Quick Launch\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: quicklaunchicon

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Flags: nowait postinstall skipifsilent

[Code]
// Проверка наличия MySQL
function IsMySQLInstalled: Boolean;
var
  ResultCode: Integer;
begin
  Result := Exec('mysql', '--version', '', SW_HIDE, ewWaitUntilTerminated, ResultCode);
end;

// Предупреждение если MySQL не установлен
function InitializeSetup(): Boolean;
begin
  Result := True;
  if not IsMySQLInstalled then
  begin
    if MsgBox('MySQL не обнаружен на вашем компьютере.' + #13#10 +
              'Приложение требует MySQL для работы.' + #13#10 + #13#10 +
              'Вы можете:' + #13#10 +
              '1. Установить MySQL отдельно' + #13#10 +
              '2. Продолжить установку (потребуется настройка позже)' + #13#10 + #13#10 +
              'Продолжить установку?',
              mbConfirmation, MB_YESNO) = IDNO then
    begin
      Result := False;
    end;
  end;
end;

// Запуск install.php после установки
procedure CurStepChanged(CurStep: TSetupStep);
var
  ResultCode: Integer;
begin
  if CurStep = ssPostInstall then
  begin
    if MsgBox('Хотите запустить мастер настройки базы данных?', mbConfirmation, MB_YESNO) = IDYES then
    begin
      // Запуск приложения для первичной настройки
      Exec(ExpandConstant('{app}\{#MyAppExeName}'), '', '', SW_SHOW, ewNoWait, ResultCode);
    end;
  end;
end;

[UninstallDelete]
Type: filesandordirs; Name: "{app}\webcache"
Type: filesandordirs; Name: "{app}\www\uploads"
Type: files; Name: "{app}\debug.log"
Type: files; Name: "{app}\www\install.lock"
